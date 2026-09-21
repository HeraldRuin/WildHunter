@extends('Layout::user')

@section('content')
    <div class="container-fluid list-animal-width custom-fluid">
        <h2 class="title-bar">
            {{ __('Edit Blogs') }}
            @if(Auth::user()->hasPermission('blog_create'))
                <a href="{{ route('blog.vendor.create') }}" class="btn-change-password">
                    <i class="fa fa-plus"></i> {{ __('Add Blog') }}
                </a>
            @endif
        </h2>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form id="bulk-form" method="post" action="{{ route('blog.vendor.bulkEdit') }}" class="filter-form filter-form-left d-flex justify-content-start">
                    {{ csrf_field() }}
                    <select name="action" class="form-control mr-2">
                        <option value="">{{ __(' Bulk Actions ') }}</option>
                        <option value="publish">{{ __(' Publish ') }}</option>
                        <option value="draft">{{ __(' Move to Draft ') }}</option>
                        <option value="delete">{{ __(' Delete ') }}</option>
                    </select>
                    <button data-confirm="{{ __('Do you want to delete?') }}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{ __('Apply') }}</button>
                </form>
            </div>
            <div class="col-left">
                <form method="get" action="{{ route('blog.vendor.index') }}" class="filter-form filter-form-right d-flex justify-content-end" role="search">
                    <input type="text" name="s" value="{{ request()->s }}" placeholder="{{ __('Search by name') }}" class="form-control mr-2">
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{ __('Search') }}</button>
                </form>
            </div>
        </div>
        <div class="text-right mb-2">
            <p><i>{{ __('Found :total items', ['total' => $rows->total()]) }}</i></p>
        </div>

        @if($rows->total() > 0)
            <div class="row blog-cards-grid">
                @foreach($rows as $row)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="blog-card panel h-100">
                            <div class="blog-card-cover">
                                @if($row->getCoverUrl())
                                    <img src="{{ $row->getCoverUrl() }}" alt="{{ $row->title }}">
                                @else
                                    <div class="blog-card-cover-placeholder">
                                        <i class="fa fa-newspaper-o"></i>
                                    </div>
                                @endif
                                <span class="blog-card-status badge badge-{{ $row->status === 'publish' ? 'success' : 'secondary' }}">
                                    {{ $row->status === 'publish' ? __('Published') : __('Draft') }}
                                </span>
                            </div>
                            <div class="blog-card-body panel-body">
                                <h5 class="blog-card-title">
                                    <a href="{{ route('blog.vendor.edit', ['id' => $row->id]) }}">{{ $row->title ?: __('Untitled') }}</a>
                                </h5>
                                <p class="blog-card-date text-muted mb-2">
                                    <i class="fa fa-clock-o"></i> {{ display_date($row->updated_at) }}
                                </p>
                                <div class="blog-card-actions mt-auto">
                                    <a href="{{ route('blog.vendor.edit', ['id' => $row->id]) }}" class="btn btn-primary btn-sm btn-block">
                                        <i class="fa fa-edit"></i> {{ __('Edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{ $rows->appends(request()->query())->links() }}
        @else
            <div class="panel">
                <div class="panel-body text-center py-5">
                    <i class="fa fa-newspaper-o fa-3x text-muted mb-3"></i>
                    <p class="text-muted">{{ __('No blogs found') }}</p>
                    @if(Auth::user()->hasPermission('blog_create'))
                        <a href="{{ route('blog.vendor.create') }}" class="btn btn-primary">{{ __('Create your first blog') }}</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

@include('Blog::partials.vendor-user-layout')

@push('css')
    <style>
        .user-form-settings .title-bar {
            width: 100% !important;
        }
        .blog-cards-grid .blog-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .blog-cards-grid .blog-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        .blog-card-cover {
            position: relative;
            height: 160px;
            overflow: hidden;
            background: #f5f5f5;
        }
        .blog-card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .blog-card-cover-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 48px;
            color: #ccc;
        }
        .blog-card-status {
            position: absolute;
            top: 8px;
            right: 8px;
        }
        .blog-card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            position: relative;
            padding-top: 16px;
        }
        .blog-card-title {
            font-size: 15px;
            margin-bottom: 8px;
        }
        .blog-card-title a {
            color: #333;
            text-decoration: none;
        }
        .blog-card-title a:hover {
            color: #007bff;
        }
        .blog-card-date { font-size: 12px; }
    </style>
@endpush
