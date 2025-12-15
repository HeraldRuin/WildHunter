<?php

    namespace Modules\User\Listeners;

    use App\User;
    use Illuminate\Support\Facades\Mail;
    use Modules\User\Emails\RegisteredEmail;
    use Modules\User\Events\SendMailUserRegistered;

    class SendMailUserRegisteredListen
    {
        /**
         * Create the event listener.
         *
         * @return void
         */
        public $user;

        const CODE = [
            'first_name' => '[first_name]',
            'last_name'  => '[last_name]',
            'name'       => '[name]',
            'email'      => '[email]',
            'login'      => '[login]',
            'password'   => '[password]',
            'button_verify' => '[button_verify]',

        ];

        public function __construct(User $user)
        {
            $this->user = $user;
            //
        }

        /**
         * Handle the event.
         *
         * @param Event $event
         * @return void
         */
        public function handle(SendMailUserRegistered $event)
        {
            if($event->user->locale){
                $old = app()->getLocale();
                app()->setLocale($event->user->locale);
            }

            if (!empty(setting_item('enable_mail_user_registered'))) {
                $body = $this->replaceContentEmail($event, setting_item_with_lang('user_content_email_registered', app()->getLocale(), '', true, true));
                Mail::to($event->user->email)->send(new RegisteredEmail($event->user, $body, 'customer', $event->password, $event->appLocale));
            }

            if(!empty($old)){
                app()->setLocale($old);
            }

            if (!empty(setting_item('admin_email') and !empty(setting_item_with_lang('admin_enable_mail_user_registered',app()->getLocale(), '', true, true)))) {
                $body = $this->replaceContentEmail($event, setting_item_with_lang('admin_content_email_user_registered',app()->getLocale(), '', true,true));
                Mail::to(setting_item('admin_email'))->send(new RegisteredEmail($event->user, $body, 'admin', null, $event->appLocale));
            }

        }

        public function replaceContentEmail($event, $content)
        {
            if (!empty($content)) {
                foreach (self::CODE as $item => $value) {
                    if ($item == "button_verify") {
                        $replacement = $this->buttonVerify($event);
                    } elseif ($item == "password") {
                        $replacement = $event->password ?? '';
                    } elseif ($item == "login") {
                        $replacement = $event->user->email;
                    } else {
                        $replacement = @$event->user->$item;
                    }
                    $content = str_replace($value, $replacement, $content);
                }
            }
            return $content;
        }
        public function buttonVerify($event)
        {
            if(!$event->user->hasVerifiedEmail()){
                $text = __('Verify Email Address');
                $button = '<a style="border-radius: 3px;
                color: #fff;
                display: inline-block;
                text-decoration: none;
                background-color: #3490dc;
                border-top: 10px solid #3490dc;
                border-right: 18px solid #3490dc;
                border-bottom: 10px solid #3490dc;
                border-left: 18px solid #3490dc;" href="' . $event->user->verificationUrl() . '">' . $text . '</a>';
                return $button;
            }
        }
    }
