@extends('Layout::user')
@section('content')
    <div class="container-fluid">
        <h2 class="title-bar">{{ $page_title }}</h2>
        <div class="row">
            <div class="col-md-12 timer-width">
                <div class="panel">
                    <div class="panel-title">
                        <strong>{{ __('Настройки таймера сбора') }}</strong>
                    </div>
                    <div class="panel-body">
                        <form action="{{ route('settings.vendor.collection-timer.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="timer_hours">{{ __('Размер таймера (часы)') }} *</label>
                                <input type="number"
                                       class="form-control"
                                       id="timer_hours"
                                       name="timer_hours"
                                       value="{{ old('timer_hours', $timer_hours) }}"
                                       min="1"
                                       required>
                                <small class="form-text text-muted">{{ __('Установите размер таймера сбора в часах (например: 24)') }}</small>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">{{ __('Сохранить') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
