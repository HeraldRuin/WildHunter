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
            'isAdmin' => is_admin(),
            'viewAdminCabinet' => $viewAdminCabinet,
        ];
    }
    public function getViewAdminCabinet(): bool
    {
        $viewAdminCabinet = $this->request->query('viewAdminCabinet');
        $AuthUser = Auth::user();

        return $viewAdminCabinet && is_admin();
    }
    public function getViewUserId(): int
    {
        return $this->request->query('user');
    }
    public function getViewUser(): int
    {
        $userId = $this->request->query('user');
        return (int) ($userId ?? Auth::id());
    }
}
