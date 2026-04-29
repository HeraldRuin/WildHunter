<?php

namespace Modules\User\Services;

use Modules\User\Models\User;

class UserService
{
    public function searchUser(string $query): array
    {
        $users = User::where('id', $query)
            ->select(['id', 'user_name', 'first_name'])
            ->get();

        return [
            'data' => $users
        ];
    }
}
