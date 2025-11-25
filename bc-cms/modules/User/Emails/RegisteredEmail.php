<?php

    namespace Modules\User\Emails;

    use App\User;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\App;

    class RegisteredEmail extends Mailable
    {
        use Queueable, SerializesModels;

        public $user;
        public $content;
        public $to_address;
        public $password;
        public $appLocale;

        public function __construct(User $user, $content, $to_address, $password = null, $appLocale = null)
        {
            $this->user = $user;
            $this->content = $content;
            $this->to_address = $to_address;
            $this->password = $password;
            $this->appLocale = $appLocale;
        }

        public function build()
        {
            App::setLocale($this->appLocale);
            $subject = __('user_registered', ['name' => $this->user->getDisplayName()]);
            return $this->subject($subject)->view('User::emails.registered')->with([
                'user'    => $this->user,
                'content' => $this->content,
                'to'      => $this->to_address,
                'login' => $this->user->email,
                'password' => $this->password,
            ]);
        }
    }
