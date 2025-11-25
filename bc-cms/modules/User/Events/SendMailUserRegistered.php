<?php
namespace Modules\User\Events;

use App\Notifications\AdminChannelServices;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendMailUserRegistered
{
    use SerializesModels;
    public $user;
    public $password;
    public $appLocale;

    public function __construct($user, $password, $appLocale)
    {
        $this->user = $user;
        $this->password = $password;
        $this->appLocale = $appLocale;
    }
}
