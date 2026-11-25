<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function pngImageBytes(int $width = 128, int $height = 128): string
    {
        $chunk = static fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        $scanline = "\x00".str_repeat("\xff\x00\x00\xff", $width);
        $pixels = str_repeat($scanline, $height);
        $header = pack('NNC5', $width, $height, 8, 6, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', $header)
            .$chunk('IDAT', gzcompress($pixels))
            .$chunk('IEND', '');
    }

    public function test_profile_update_requires_authentication(): void
    {
        $this->postJson('/api/profile', ['username' => 'new_handle'])
            ->assertUnauthorized();
    }

    public function test_registration_normalizes_username_and_keeps_older_clients_compatible(): void
    {
        $this->postJson('/api/register', [
            'name' => 'New Customer',
            'username' => 'New_Customer',
            'email' => 'new@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()
            ->assertJsonPath('data.user.username', 'new_customer');

        $response = $this->postJson('/api/register', [
            'name' => 'Legacy Customer',
            'email' => 'legacy@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $this->assertMatchesRegularExpression(
            '/\Alegacy_customer_[a-z0-9]{8}\z/',
            $response->json('data.user.username')
        );
    }

    public function test_user_can_update_username_upload_and_remove_avatar(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['username' => 'old_handle']);
        Sanctum::actingAs($user);

        $this->post('/api/profile', [
            'username' => 'NEW_Handle',
            'avatar' => UploadedFile::fake()->createWithContent('profile.png', $this->pngImageBytes()),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.username', 'new_handle')
            ->assertJsonStructure(['data' => ['avatar_url']])
            ->assertJsonMissingPath('data.avatar_path');

        $avatarPath = $user->fresh()->avatar_path;
        $this->assertNotNull($avatarPath);
        Storage::disk('local')->assertExists($avatarPath);

        $this->get(route('users.avatar', ['user' => $user->id]))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->post('/api/profile', [
            'username' => 'new_handle',
            'remove_avatar' => '1',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.avatar_url', null);

        Storage::disk('local')->assertMissing($avatarPath);
    }

    public function test_username_must_be_unique_and_svg_upload_is_rejected(): void
    {
        Storage::fake('local');
        User::factory()->create(['username' => 'taken_handle']);
        $user = User::factory()->create(['username' => 'current_handle']);
        Sanctum::actingAs($user);

        $this->post('/api/profile', [
            'username' => 'TAKEN_HANDLE',
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        $this->post('/api/profile', [
            'username' => 'current_handle',
            'avatar' => UploadedFile::fake()->createWithContent(
                'profile.svg',
                '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
            ),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');

        $this->assertSame([], Storage::disk('local')->allFiles('avatars'));
    }
}
