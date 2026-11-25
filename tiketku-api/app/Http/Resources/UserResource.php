<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'avatar_url' => $this->avatar_path
                ? route('users.avatar', ['user' => $this->id]).'?v='.hash('sha256', $this->avatar_path)
                : null,
        ];
    }
}
