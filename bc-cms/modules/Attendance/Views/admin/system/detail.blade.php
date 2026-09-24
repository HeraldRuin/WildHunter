@extends('admin.layouts.app')

@section('content')
    <form action="{{ route('additional_system.admin.store', ['id' => $row->id ?: '-1']) }}" method="post">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <h1 class="title-bar">{{ $row->id ? __('Edit system service') : __('Add system service') }}</h1>
            </div>
            @include('admin.message')
            <div class="row">
                <div class="col-md-9">
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('Service name') }}</strong></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>{{ __('Service name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $row->name) }}" required class="form-control" placeholder="{{ __('Service name') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('Save Changes') }}</strong></div>
                        <div class="panel-body">
                            <div class="text-right">
                                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{ __('Save Changes') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
