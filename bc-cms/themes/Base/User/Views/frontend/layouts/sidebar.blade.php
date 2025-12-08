<?php

use Illuminate\Support\Facades\Route;

$dataUser = $user ?? Auth::user();

$viewAdminCabinet = $viewAdminCabinet ?? false;
$isAdmin = $isAdmin ?? false;

$menus = [
    'dashboard' => [
        'url' => route("vendor.dashboard"),
        'title' => __("Dashboard"),
        'icon' => 'fa fa-home',
        'permission' => 'dashboard_vendor_access',
        'position' => 10
    ],
    'booking-history' => [
        'url' => route("user.booking_history"),
        'title' => __("Booking History"),
        'icon' => 'fa fa-clock-o',
        'position' => 20
    ],
    "wishlist" => [
        'url' => route("user.wishList.index"),
        'title' => __("Wishlist"),
        'icon' => 'fa fa-heart-o',
        'position' => 21
    ],
    'profile' => [
        'url' => route("user.profile.index"),
        'title' => __("My Profile"),
        'icon' => 'fa fa-cogs',
        'position' => 22
    ],
    'password' => [
        'url' => route("user.change_password"),
        'title' => __("Change password"),
        'icon' => 'fa fa-lock',
        'position' => 100
    ],
    'admin' => [
        'url' => route('admin.index'),
        'title' => __("Admin Dashboard"),
        'icon' => 'icon ion-ios-ribbon',
        'permission' => 'dashboard_access',
        'position' => 110
    ]
];

if (!$isAdmin) {
    unset($menus['admin']);
}

if ($isAdmin && $viewAdminCabinet) {
    unset($menus['dashboard']);
}
if ($isAdmin) {
    unset($menus['password']);
    unset($menus['profile']);
    unset($menus['admin']);
}

// Modules
$custom_modules = \Modules\ServiceProvider::getActivatedModules();

if (!empty($custom_modules)) {

    foreach ($custom_modules as $module) {
        $moduleClass = $module['class'];
        if (class_exists($moduleClass)) {
            $menuConfig = call_user_func([$moduleClass, 'getUserMenu']);

            if (!empty($menuConfig)) {
                $menus = array_merge($menus, $menuConfig);
            }
            $menuSubMenu = call_user_func([$moduleClass,'getUserSubMenu']);
            if(!empty($menuSubMenu)){
                foreach($menuSubMenu as $k=>$submenu){
                    $submenu['id'] = $submenu['id'] ?? '_'.$k;
                    if(!empty($submenu['parent']) and isset($menus[$submenu['parent']])){
                        $menus[$submenu['parent']]['children'][$submenu['id']] = $submenu;
                        $menus[$submenu['parent']]['children'] = array_values(\Illuminate\Support\Arr::sort($menus[$submenu['parent']]['children'], function ($value) {
                            return $value['position'] ?? 100;
                        }));
                    }
                }
            }
        }
    }
}

// Plugins Menu
$plugins_modules = \Plugins\ServiceProvider::getModules();
if(!empty($plugins_modules)){
    foreach($plugins_modules as $module){
        $moduleClass = "\\Plugins\\".ucfirst($module)."\\ModuleProvider";
        if(class_exists($moduleClass))
        {
            $menuConfig = call_user_func([$moduleClass,'getUserMenu']);
            if(!empty($menuConfig)){
                $menus = array_merge($menus,$menuConfig);
            }
            $menuSubMenu = call_user_func([$moduleClass,'getUserSubMenu']);
            if(!empty($menuSubMenu)){
                foreach($menuSubMenu as $k=>$submenu){
                    $submenu['id'] = $submenu['id'] ?? '_'.$k;
                    if(!empty($submenu['parent']) and isset($menus[$submenu['parent']])){
                        $menus[$submenu['parent']]['children'][$submenu['id']] = $submenu;
                        $menus[$submenu['parent']]['children'] = array_values(\Illuminate\Support\Arr::sort($menus[$submenu['parent']]['children'], function ($value) {
                            return $value['position'] ?? 100;
                        }));
                    }
                }
            }
        }
    }
}

// Custom Menu
$custom_modules = \Custom\ServiceProvider::getModules();
if(!empty($custom_modules)){
    foreach($custom_modules as $module){
        $moduleClass = "\\Custom\\".ucfirst($module)."\\ModuleProvider";
        if(class_exists($moduleClass))
        {
            $menuConfig = call_user_func([$moduleClass,'getUserMenu']);
            if(!empty($menuConfig)){
                $menus = array_merge($menus,$menuConfig);
            }
            $menuSubMenu = call_user_func([$moduleClass,'getUserSubMenu']);
            if(!empty($menuSubMenu)){
                foreach($menuSubMenu as $k=>$submenu){
                    $submenu['id'] = $submenu['id'] ?? '_'.$k;
                    if(!empty($submenu['parent']) and isset($menus[$submenu['parent']])){
                        $menus[$submenu['parent']]['children'][$submenu['id']] = $submenu;
                        $menus[$submenu['parent']]['children'] = array_values(\Illuminate\Support\Arr::sort($menus[$submenu['parent']]['children'], function ($value) {
                            return $value['position'] ?? 100;
                        }));
                    }
                }
            }
        }
    }
}

$currentUrl = url(Illuminate\Support\Facades\Route::current()->uri());
if (!empty($menus))
    $menus = array_values(\Illuminate\Support\Arr::sort($menus, function ($value) {
        return $value['position'] ?? 100;
    }));

foreach ($menus as $k => $menuItem) {

    if (!empty($menuItem['permission']) && !$dataUser->hasPermission($menuItem['permission'])) {
        unset($menus[$k]);
        continue;
    }

    $params = $menuItem['params'] ?? [];
    $params['user'] = $dataUser->id;
    $params['viewAdminCabinet'] = $viewAdminCabinet;

    if (Route::has($menuItem['url'])) {
        $menuUrl = route($menuItem['url'], $params);
    } else {
        $menuUrl = url($menuItem['url']) . '?' . http_build_query($params);
    }

    $menus[$k]['url'] = $menuUrl;
    $menus[$k]['class'] = $currentUrl == $menuUrl ? 'active' : '';

    if (!empty($menuItem['children'])) {
        $menus[$k]['class'] .= ' has-children';
        foreach ($menuItem['children'] as $k2 => $menuItem2) {

            if (!empty($menuItem2['permission']) && !$dataUser->hasPermission($menuItem2['permission'])) {
                unset($menus[$k]['children'][$k2]);
                continue;
            }

            $childParams = $menuItem2['params'] ?? [];
            $childParams['user'] = $dataUser->id;
            $params['viewAdminCabinet'] = $viewAdminCabinet;

            if (Route::has($menuItem2['url'])) {
                $childUrl = route($menuItem2['url'], $childParams);
            } else {
                $childUrl = url($menuItem2['url']) . '?' . http_build_query($childParams);
            }

            $menus[$k]['children'][$k2]['url'] = $childUrl;
            $menus[$k]['children'][$k2]['class'] = $currentUrl == $childUrl ? 'active active_child' : '';
        }
    }
}
?>
<div class="sidebar-user">
    <div class="bc-close-menu-user"><i class="icofont-scroll-left"></i></div>
    <div class="logo">
        @if($avatar_url = $dataUser->getAvatarUrl())
            <div class="avatar avatar-cover" style="background-image: url('{{$dataUser->getAvatarUrl()}}')"></div>
        @else
            <span class="avatar-text">{{ucfirst($dataUser->getDisplayName()[0])}}</span>
        @endif
    </div>
    <div class="user-profile-avatar">
        <div class="info-new">
            <span class="role-name badge badge-info">{{$dataUser->role_name}}</span>
            <h5>{{$dataUser->getDisplayName()}}</h5>
            <p>{{ __("Member Since :time",["time"=> date("M Y",strtotime($dataUser->created_at))]) }}</p>
        </div>
    </div>
    <div class="user-profile-plan">
        {{--        @if( !Auth::user()->hasPermission("dashboard_vendor_access") and setting_item('vendor_enable'))--}}
        {{--            <a href=" {{ route("user.upgrade_vendor") }}">{{ __("Become a vendor") }}</a>--}}
        {{--        @endif--}}
    </div>
    <div class="sidebar-menu">
        <ul class="main-menu">
            @foreach($menus as $menuItem)
                <li class="{{$menuItem['class']}}" position="{{$menuItem['position'] ?? ""}}">
                    <a href="{{ url($menuItem['url']) }}">
                        @if(!empty($menuItem['icon']))
                            <span class="icon text-center"><i class="{{$menuItem['icon']}}"></i></span>
                        @endif
                        {!! clean($menuItem['title']) !!}

                    </a>
                    @if(!empty($menuItem['children']))
                        <i class="caret"></i>
                    @endif
                    @if(!empty($menuItem['children']))
                        <ul class="children">
                            @foreach($menuItem['children'] as $menuItem2)
                                <li class="{{$menuItem2['class']}}"><a href="{{ url($menuItem2['url']) }}">
                                        @if(!empty($menuItem2['icon']))
                                            <i class="{{$menuItem2['icon']}}"></i>
                                        @endif
                                        {!! clean($menuItem2['title']) !!}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <div class="logout">
        <form id="logout-form-vendor" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
        @if(!$viewAdminCabinet)
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-vendor').submit();"><i
                    class="fa fa-sign-out"></i> {{__("Log Out")}}
            </a>
        @endif
    </div>
    <div class="logout">
        @if($viewAdminCabinet && $isAdmin)
            <a href="{{ url('/admin/module/hotel') }}" style="color: #1ABC9C">
                <i class="fa fa-long-arrow-left"></i> {{ __("Back to Hotels") }}
            </a>
        @else
            <a href="{{ url('/') }}" style="color: #1ABC9C">
                <i class="fa fa-long-arrow-left"></i> {{ __("Back to Homepage") }}
            </a>
        @endif
    </div>

</div>
