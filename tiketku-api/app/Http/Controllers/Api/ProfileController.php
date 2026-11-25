<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $username = $request->input('username');
        if (is_string($username)) {
            $request->merge(['username' => mb_strtolower(trim($username))]);
        }

        $user = $request->user();
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'avatar' => [
                'nullable',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048',
                'dimensions:min_width=64,min_height=64,max_width=2048,max_height=2048',
            ],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('remove_avatar') && $request->hasFile('avatar')) {
            throw ValidationException::withMessages([
                'avatar' => 'Pilih foto baru atau hapus foto lama, jangan keduanya.',
            ]);
        }

        $newPath = null;
        if ($request->hasFile('avatar')) {
            $newPath = $request->file('avatar')->store('avatars', 'local');
            if ($newPath === false) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }
        }

        $previousPath = $user->avatar_path;
        $nextPath = $request->boolean('remove_avatar') ? null : ($newPath ?? $previousPath);

        try {
            $user = DB::transaction(function () use ($user, $data, $nextPath) {
                $user->username = $data['username'];
                $user->avatar_path = $nextPath;
                $user->save();

                return $user->fresh();
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('local')->delete($newPath);
            }

            throw $exception;
        }

        if (
            $previousPath !== null
            && $previousPath !== $user->avatar_path
            && str_starts_with($previousPath, 'avatars/')
        ) {
            Storage::disk('local')->delete($previousPath);
        }

        return response()->json(['data' => UserResource::make($user)]);
    }

    public function avatar(User $user)
    {
        abort_unless(
            $user->avatar_path !== null
                && str_starts_with($user->avatar_path, 'avatars/')
                && Storage::disk('local')->exists($user->avatar_path),
            404
        );

        return Storage::disk('local')->response(
            $user->avatar_path,
            null,
            [
                'Cache-Control' => 'public, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline'
        );
    }
}
