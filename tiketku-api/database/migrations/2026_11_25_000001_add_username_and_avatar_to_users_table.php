<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique();
            $table->string('avatar_path')->nullable();
        });

        $used = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $base = Str::slug($user->name, '_');
            $base = mb_substr($base !== '' ? $base : 'user', 0, 30);
            $username = $base;
            $suffix = 1;

            while (isset($used[mb_strtolower($username)])) {
                $tail = '_'.$suffix++;
                $username = mb_substr($base, 0, 30 - mb_strlen($tail)).$tail;
            }

            $used[mb_strtolower($username)] = true;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'avatar_path']);
        });
    }
};
