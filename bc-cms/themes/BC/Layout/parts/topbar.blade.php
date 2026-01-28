@if (defined('BC_PRO_NOTIFY_ENABLE') && BC_PRO_NOTIFY_ENABLE)
    <div class="bc-topbar-pro">
        <div class="container">
            <a href="/intro-pro "> {{ __('Upgrade to PRO to unlock unlimited access to all of our features') }}</a>
        </div>
    </div>
@endif
<div class="bc_topbar">
    <div class="container">
        <div class="content">
            <div class="topbar-left">
{{--                {!! clean(setting_item_with_lang('topbar_left_text')) !!}--}}
            </div>
            <div class="topbar-right">
                <ul class="topbar-items">
{{--                    @include('Core::frontend.currency-switcher')--}}
{{--                    @include('Language::frontend.switcher')--}}
                    @if (!Auth::check())
                        <li class="login-item">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#login">
                                {{ __('Login') }}
                            </button>
{{--                            <a href="#login" data-toggle="modal" data-target="#login"--}}
{{--                                class="login">{{ __('Login') }}</a>--}}
                        </li>
                        @if (is_enable_registration())
                            <li class="signup-item">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#register">
                                    {{ __('Sign Up') }}
                                </button>
{{--                                <a href="#register" data-toggle="modal" data-target="#register"--}}
{{--                                    class="signup">{{ __('Sign Up') }}</a>--}}
                            </li>
                        @endif
                    @else
                        @include('Layout::parts.notification')
                        <li class="login-item dropdown">
                            <a href="#" id="user-dropdown-toggle" class="login">
                                {{ __('Hi, :name', ['name' => Auth::user()->getDisplayName()]) }} <i class="fa fa-angle-down"></i>
                            </a>

                            <form id="logout-form-topbar" action="{{ route('logout') }}" method="POST"
                                style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
