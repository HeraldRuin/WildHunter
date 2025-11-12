@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="mb40">
            <div class="d-flex justify-content-between">
                <h1 class="title-bar">{{ $group['name'] }}</h1>
            </div>
            <hr>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-2">
                <div class="card sticky-top" style="top: 70px; z-index: 100;">
                    <div class="card-header d-flex align-items-center">
                        <strong>
                            <i class="fa fa-cogs"></i> {{ __('Main Settings') }}</strong>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($groups as $id => $setting)
                            <a class="list-group-item list-group-item-action @if ($current_group == $id) active @endif"
                                href="{{ route('core.admin.settings.index', ['group' => $id]) }}">
                                @if (!empty($setting['icon']))
                                    <i class="{{ $setting['icon'] }}"></i>
                                @endif
                                {{ $setting['title'] }}
                                @if (!empty($setting['is_pro']))
                                    <span class="badge badge-warning ml-1" style="width: auto">PRO</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <form action="{{ route('core.admin.settings.store', ['group' => $current_group]) }}" method="post"
                    autocomplete="off">
                    @csrf
                    <div class="sticky-top @if (!setting_item('site_enable_multi_lang')) panel px-3 py-2 d-flex justify-content-end @endif"
                        style="top: {{ setting_item('site_enable_multi_lang') ? '56px' : '70px' }}; z-index: 100;">
                        @include('Language::admin.navigation')
                        <div class=" @if (setting_item('site_enable_multi_lang')) position-absolute @endif"
                            style="right: 10px; top: 9px;">

                            <button class="btn btn-success" type="submit"><i class="fa fa-save"></i>
                                {{ __('Save settings') }}</button>
                        </div>
                    </div>

                    <div class="lang-content-box">
                        @if (empty($group['view']))
                            @include ('Core::admin.settings.groups.' . $current_group)
                        @else
                            @include ($group['view'])
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
