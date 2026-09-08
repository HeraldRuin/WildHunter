@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('All Blogs') }}</h1>
            <div class="title-actions">
                <a href="{{ route('blog.admin.create') }}" class="btn btn-primary">{{ __('Add Blog') }}</a>
            </div>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                <form method="post" action="#" class="filter-form filter-form-left d-flex justify-content-start">
                    {{ csrf_field() }}
                    <select name="action" class="form-control">
                        <option value="">{{ __(' Bulk Actions ') }}</option>
                        <option value="publish">{{ __(' Publish ') }}</option>
                        <option value="draft">{{ __(' Move to Draft ') }}</option>
                        <option value="delete">{{ __(' Delete ') }}</option>
                    </select>
                    <button data-confirm="{{ __('Do you want to delete?') }}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{ __('Apply') }}</button>
                </form>
            </div>
            <div class="col-left dropdown">
                <form method="get" action="{{ route('blog.admin.index') }}" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">
                    <input type="text" name="s" value="{{ request()->s }}" placeholder="{{ __('Search by name') }}" class="form-control">
                    <div class="ml-3 position-relative">
                        <button class="btn btn-secondary dropdown-toggle bc-dropdown-toggle-filter" type="button" id="dropdown_filters">
                            {{ __('Advanced') }}
                        </button>
                        <div class="dropdown-menu px-3 py-3 dropdown-menu-right" aria-labelledby="dropdown_filters">
                            @include('Core::admin.global.advanced-filter')
                        </div>
                    </div>
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{ __('Search') }}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{ __('Found :total items', ['total' => $rows->total()]) }}</i></p>
        </div>
        <div class="panel">
            <div class="panel-body">
                <form action="" class="bc-form-item">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th width="60px"><input type="checkbox" class="check-all"></th>
                                <th>{{ __('Name') }}</th>
                                <th width="100px">{{ __('Date') }}</th>
                                <th width="100px"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($rows->total() > 0)
                                @foreach($rows as $row)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" class="check-item" value="{{ $row->id }}"></td>
                                        <td class="title">
                                            <a href="{{ route('blog.admin.create') }}">{{ $row->title }}</a>
                                        </td>
                                        <td>{{ display_date($row->updated_at) }}</td>
                                        <td>
                                            <a href="{{ route('blog.admin.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> {{ __('Edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4">{{ __('No blogs found') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </form>
                {{ $rows->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
