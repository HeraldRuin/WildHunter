<?php

    namespace Modules\User\Listeners;

    use App\User;
    use Illuminate\Support\Facades\Mail;
    use Modules\User\Emails\UpdatePasswordEmail;
    use Modules\User\Events\SendMailUserUpdatePassword;

    class SendMailUserUpdatePasswordListen
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
            'email'      => '[email]',
            'login'      => '[login]',
            'password'   => '[password]',
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
        public function handle(SendMailUserUpdatePassword $event)
        {
            if($event->user->locale){
                $old = app()->getLocale();
                app()->setLocale($event->user->locale);
            }

                $body = $this->replaceContentEmail($event, setting_item_with_lang('user_content_update_password', app()->getLocale(), '', true, true));
                Mail::to($event->user->email)->send(new UpdatePasswordEmail($event->user, $body, 'customer', $event->user->current_password, app()->getLocale()));

            if(!empty($old)){
                app()->setLocale($old);
            }
        }

        public function replaceContentEmail($event, $content)
        {
            if (!empty($content)) {
                foreach (self::CODE as $item => $value) {
                    if ($item == "password") {
                        $replacement = $event->user->current_password;
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
    }
