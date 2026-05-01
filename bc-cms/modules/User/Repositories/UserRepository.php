<?php

namespace Modules\User\Repositories;

use App\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function searchHunters(string $query): Collection
    {
        return User::query()
            ->whereNotIn('role_id', function ($q) {
                $q->select('id')
                    ->from('core_roles')
                    ->whereIn('code', ['administrator']);
            })
            ->where(function ($q) use ($query) {
                $q->where('user_name', 'LIKE', $query.'%')
                    ->orWhere('first_name', 'LIKE', $query.'%')
                    ->orWhere('last_name', 'LIKE', $query.'%')
                    ->orWhere('email', 'LIKE', $query.'%')
                    ->orWhere('id', 'LIKE', $query.'%');
            })
            ->limit(10)
            ->get(['id','user_name','first_name','last_name','email','phone']);
    }
}
