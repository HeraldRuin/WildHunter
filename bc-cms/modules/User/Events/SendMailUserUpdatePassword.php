<?php
namespace Modules\User\Events;

use App\Notifications\AdminChannelServices;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendMailUserUpdatePassword
{
    use SerializesModels;
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
