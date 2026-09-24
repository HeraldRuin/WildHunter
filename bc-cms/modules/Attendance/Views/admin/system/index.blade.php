@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('System services') }}</h1>
            <div class="title-actions">
                <a href="{{ route('additional_system.admin.create') }}" class="btn btn-primary">{{ __('Add system service') }}</a>
            </div>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                @if($rows->total() > 0)
                    <form method="post" action="{{ route('additional_system.admin.bulkEdit') }}" class="filter-form filter-form-left d-flex justify-content-start">
                        {{ csrf_field() }}
                        <select name="action" class="form-control">
                            <option value="">{{ __(' Bulk Actions ') }}</option>
                            <option value="delete">{{ __(' Delete ') }}</option>
                        </select>
                        <button data-confirm="{{ __('Do you want to delete?') }}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{ __('Apply') }}</button>
                    </form>
                @endif
            </div>
            <div class="col-left">
                <form method="get" action="{{ route('additional_system.admin.index') }}" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">
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
                                <th width="60px"><input type="checkbox" class="check-all"></th>
                                <th>{{ __('Service name') }}</th>
                                <th width="160px">{{ __('Date') }}</th>
                                <th width="280px"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($rows->total() > 0)
                                @foreach($rows as $row)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" class="check-item" value="{{ $row->id }}"></td>
                                        <td class="title">
                                            <a href="{{ route('additional_system.admin.edit', ['id' => $row->id]) }}">{{ $row->name }}</a>
                                        </td>
                                        <td>{{ $row->updated_at ? display_date($row->updated_at) : '' }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('additional_system.admin.edit', ['id' => $row->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                            <form method="post" action="{{ route('additional_system.admin.bulkEdit') }}" class="d-inline-block mb-0 ml-1" onsubmit="return confirm(@json(__('Delete this service?')))">
                                                @csrf
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ids[]" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i> {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4">{{ __('No system services found') }}</td>
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
