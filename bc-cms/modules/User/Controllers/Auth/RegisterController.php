<?php


	namespace Modules\User\Controllers\Auth;


	use App\Helpers\ReCaptchaEngine;
    use Illuminate\Auth\Events\Registered;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\MessageBag;
    use Illuminate\Validation\Rules\Password;
    use Matrix\Exception;
    use Modules\User\Events\SendMailUserRegistered;

    class RegisterController extends \App\Http\Controllers\Auth\RegisterController
	{

	    public function register(Request $request)
        {
            if(!is_enable_registration()){
                return $this->sendError(__("You are not allowed to register"));
            }
            $rules = [
                'first_name' => ['required','string','max:255'],
                'last_name'  => ['required','string','max:255'],
                'role'  => ['required','in:hunter,baseadmin'],
                'email'      => ['required','string','email','max:255','unique:users'],
                'password'   => [
                    'required',
                    'string',
                    Password::min(8)->uncompromised(),
                    function ($attribute, $value, $fail) {

                        if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value)) {
                            $fail(__('The password field must contain at least one uppercase and one lowercase letter'));
                        }
                        if (!preg_match('/[0-9]/', $value)) {
                            $fail(__('The password must contain at least one number'));
                        }
                    },
                ],
                'phone' => ['required','unique:users'],
                'term'  => ['required'],
            ];
            $messages = [
                'phone.required'      => __('Phone is required field'),
                'email.required'      => __('Email is required field'),
                'email.email'         => __('Email invalidate'),
                'email.unique'        => __('This email is already taken'),
                'password.required'   => __('Password is required field'),
                'password.min'       => __('The password must be at least 8 characters'),
                'password.uncompromised' => __('The password was found in a data leak, please use a different one'),
                'first_name.required' => __('The first name is required field'),
                'last_name.required'  => __('The last name is required field'),
                'term.required'       => __('The terms and conditions field is required'),
                'role.required'       => __('Role is required field'),
            ];
            if (ReCaptchaEngine::isEnable() and setting_item("user_enable_register_recaptcha")) {
                $codeCapcha = $request->input('g-recaptcha-response');
                if (!$codeCapcha or !ReCaptchaEngine::verify($codeCapcha)) {
                    $errors = new MessageBag(['message_error' => __('Please verify the captcha')]);
                    return response()->json([
                        'error'    => true,
                        'messages' => $errors
                    ], 200);
                }
            }
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'error'    => true,
                    'messages' => $validator->errors()
                ], 200);
            } else {

                $user = \App\User::create([
                    'first_name' => $request->input('first_name'),
                    'last_name'  => $request->input('last_name'),
                    'email'      => $request->input('email'),
                    'password'   => Hash::make($request->input('password')),
                    'status'    => $request->input('publish','publish'),
                    'phone'    => $request->input('phone'),
                    'locale'   => setting_item('site_locale') ?? 'en',
                ]);

                event(new Registered($user));
                Auth::loginUsingId($user->id);
                try {
                    event(new SendMailUserRegistered($user, $request->input('password'), app()->getLocale()));

                    if (! $user->hasVerifiedEmail()) {
                        $user->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
                    }
                } catch (Exception $exception) {

                    Log::warning("SendMailUserRegistered: " . $exception->getMessage());
                }

                $user->assignRole($request->input('role'));
                return response()->json([
                    'error'    => false,
                    'messages' => false,
                    'redirect' => $request->input('redirect') ?? $request->headers->get('referer') ?? url(app_get_locale(false, '/'))
                ], 200);
            }
        }
    }
