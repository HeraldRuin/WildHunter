<?php

namespace Modules\User\Services;

use App\User;

class UserService
{
    public function searchUserById(string $query)
    {
        return  User::where('id', 'like', "%{$query}%")
            ->select(['id', 'user_name', 'first_name', 'last_name'])
            ->get();
    }
    public function searchUserByQuery(string $query)
    {
        return  User::where(function ($q) use ($query) {
        $q->where('user_name', 'LIKE', $query.'%')
            ->orWhere('first_name', 'LIKE', $query.'%')
            ->orWhere('last_name', 'LIKE', $query.'%')
            ->orWhere('email', 'LIKE', $query.'%')
            ->orWhere('id', 'LIKE', $query.'%');
    })
            ->select(['id', 'user_name', 'first_name', 'last_name'])
            ->get();
    }
}
