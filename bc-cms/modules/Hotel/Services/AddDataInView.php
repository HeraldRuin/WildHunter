<?php

namespace Modules\Hotel\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class AddDataInView
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     *
     * @return array
     */
    public function getCabinetData(): array
    {
        $userId = $this->request->query('user');
        $viewAdminCabinet = $this->request->query('viewAdminCabinet');

        $user = User::find($userId);
        $AuthUser = Auth::user();

        return [
            'user' => $user,
            'AuthUser' => $AuthUser,
            'isAdmin' => $AuthUser->hasRole('administrator'),
            'viewAdminCabinet' => $viewAdminCabinet,
        ];
    }
    public function getViewAdminCabinet(): bool
    {
        $viewAdminCabinet = $this->request->query('viewAdminCabinet');
        $AuthUser = Auth::user();

        return $viewAdminCabinet && $AuthUser->hasRole('administrator');
    }
    public function getViewUserId(): int
    {
        return $this->request->query('user');
    }
}
