@extends('Layout::user')

@section('content')
    <div class="container-fluid list-animal-width custom-fluid">
        <h2 class="title-bar">
            {{ __('Edit Blogs') }}
            @if(Auth::user()->hasPermission('blog_create'))
                <a href="#" class="btn-change-password" id="blog-add-card-btn">
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

        <div class="row blog-cards-grid" id="blog-cards-grid">
            @if(Auth::user()->hasPermission('blog_create'))
                <div class="col-md-4 col-lg-3 mb-4" id="blog-create-card" hidden>
                    <div class="blog-card panel h-100 blog-card-create">
                        <div class="blog-card-cover blog-card-cover-pick" data-id="0">
                            <div class="blog-card-cover-placeholder">
                                <i class="fa fa-image"></i>
                                <span>{{ __('Add cover') }}</span>
                            </div>
                        </div>
                        <div class="blog-card-body panel-body">
                            <textarea
                                class="form-control blog-card-title-input"
                                data-id="0"
                                placeholder="{{ __('Blog name') }}"
                                maxlength="255"
                                rows="1"
                            ></textarea>
                            <div class="blog-card-actions mt-auto">
                                <button type="button" class="btn btn-primary btn-sm btn-block" id="blog-create-save">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @foreach($rows as $row)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="blog-card panel h-100" data-blog-id="{{ $row->id }}">
                        <div class="blog-card-cover blog-card-cover-pick" data-id="{{ $row->id }}" data-image-id="{{ $row->image_id }}">
                            @if($row->getCoverUrl())
                                <img src="{{ $row->getCoverUrl() }}" alt="{{ $row->title }}">
                            @else
                                <div class="blog-card-cover-placeholder">
                                    <i class="fa fa-image"></i>
                                    <span>{{ __('Add cover') }}</span>
                                </div>
                            @endif
                            <span class="blog-card-date">{{ display_date($row->updated_at) }}</span>
                            <span class="blog-card-status badge badge-{{ $row->status === 'publish' ? 'success' : 'secondary' }}">
                                {{ $row->status === 'publish' ? __('Published') : __('Draft') }}
                            </span>
                        </div>
                        <div class="blog-card-body panel-body">
                            <textarea
                                class="form-control blog-card-title-input"
                                data-id="{{ $row->id }}"
                                placeholder="{{ __('Blog name') }}"
                                maxlength="255"
                                rows="1"
                            >{{ $row->title }}</textarea>
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
        @if($rows->total() > 0)
            {{ $rows->appends(request()->query())->links() }}
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
        .blog-card-cover-pick {
            cursor: pointer;
        }
        .blog-card-cover-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 32px;
            color: #ccc;
            gap: 8px;
        }
        .blog-card-cover-placeholder span {
            font-size: 13px;
        }
        .blog-card-title-input {
            border: none;
            box-shadow: none !important;
            padding: 0;
            font-size: 15px;
            font-weight: 600;
            height: auto !important;
            min-height: 0;
            line-height: 1.35;
            background: transparent;
            resize: none;
            overflow: hidden;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .blog-card-title-input:focus {
            border-bottom: 1px solid #ced4da;
            border-radius: 0;
        }
        .blog-card-date {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 1;
            font-size: 12px;
            line-height: 1.2;
            color: #fff;
            background: rgba(26, 43, 71, 0.65);
            padding: 3px 8px;
            border-radius: 4px;
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
    </style>
@endpush

@push('js')
    <script>
        (function ($) {
            var metaUrl = @json(route('blog.vendor.storeMeta', ['id' => 0]));
            var token = @json(csrf_token());

            function saveMeta(id, payload, done) {
                var url = metaUrl.replace(/\/0$/, '/' + (id || 0));
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $.extend({ _token: token }, payload),
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            if (typeof done === 'function') done(res);
                        } else if (window.bookingCoreApp) {
                            bookingCoreApp.showError(res);
                        }
                    },
                    error: function (e) {
                        if (window.bookingCoreApp) bookingCoreApp.showAjaxError(e);
                    }
                });
            }

            function pickCover($cover) {
                if (typeof uploaderModal === 'undefined') return;
                uploaderModal.show({
                    multiple: false,
                    file_type: 'image',
                    onSelect: function (files) {
                        if (!files.length) return;
                        var id = $cover.data('id') || 0;
                        saveMeta(id, { image_id: files[0].id }, function (res) {
                            if (!id && res.id) {
                                window.location.reload();
                                return;
                            }
                            $cover.data('id', res.id);
                            $cover.data('image-id', files[0].id);
                            var src = files[0].thumb_size || files[0].max_large_size || res.cover_url;
                            $cover.find('img').remove();
                            $cover.find('.blog-card-cover-placeholder').remove();
                            $cover.prepend('<img src="' + src + '" alt="">');
                        });
                    }
                });
            }

            function fitTitle(el) {
                el.style.height = 'auto';
                el.style.height = el.scrollHeight + 'px';
            }

            $('.blog-card-title-input').each(function () {
                $(this).data('saved', $.trim($(this).val()));
                fitTitle(this);
            });

            $(document).on('input', '.blog-card-title-input', function () {
                fitTitle(this);
            });

            $('#blog-add-card-btn').on('click', function (e) {
                e.preventDefault();
                $('#blog-create-card').prop('hidden', false);
                $('#blog-create-card .blog-card-title-input').focus();
            });

            $(document).on('click', '.blog-card-cover-pick', function () {
                pickCover($(this));
            });

            $(document).on('change blur', '.blog-card-title-input', function () {
                var $input = $(this);
                var id = $input.data('id') || 0;
                var title = $.trim($input.val());
                if (!id && !title) return;
                if (id && title === $input.data('saved')) return;
                saveMeta(id, { title: title }, function (res) {
                    $input.data('saved', title);
                    if (!id && res.id) {
                        window.location.reload();
                    }
                });
            });

            $('#blog-create-save').on('click', function () {
                var $card = $('#blog-create-card');
                var title = $.trim($card.find('.blog-card-title-input').val());
                saveMeta(0, { title: title }, function () {
                    window.location.reload();
                });
            });
        })(jQuery);
    </script>
@endpush
