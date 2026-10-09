@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Питание') }}</h1>
            <div class="title-actions">
                <a href="{{ route('meal.admin.create') }}" class="btn btn-primary">{{ __('Add meal') }}</a>
            </div>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-end">
            <div class="col-left">
                <form method="get" action="{{ route('meal.admin.index') }}" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">
                    <input type="text" name="s" value="{{ request()->input('s') }}" placeholder="{{ __('Search by name') }}" class="form-control">
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{ __('Search') }}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{ __('Found :total items', ['total' => $rows->total()]) }}</i></p>
        </div>
        <div class="panel">
            <div class="panel-body">
                <div class="bc-form-item">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('Service name') }}</th>
                                <th width="160px">{{ __('Date') }}</th>
                                <th width="140px"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($rows->total() > 0)
                                @foreach($rows as $row)
                                    <tr>
                                        <td class="title">
                                            <a href="{{ route('meal.admin.edit', ['id' => $row->id]) }}">{{ $row->name }}</a>
                                        </td>
                                        <td>{{ $row->updated_at ? display_date($row->updated_at) : '' }}</td>
                                        <td>
                                            <a href="{{ route('meal.admin.edit', ['id' => $row->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3">{{ __('Питание не найдено') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $rows->links() }}
            </div>
        </div>
    </div>
@endsection
