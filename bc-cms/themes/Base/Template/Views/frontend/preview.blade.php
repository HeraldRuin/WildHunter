@extends('Layout::app')
@section('content')
    <div class="page-template-content">
        {{ $translation->getProcessedContent(['preview'=>1]) }}
    </div>
@endsection
@push('css')
    <link
        rel="stylesheet"
        href="{{asset('module/template/preview/dist/css/app.css?_v='.config('app.asset_version'))}}"
    />
@endpush
@push('js')
    <script>
        var template_id = {{$row->id ?? 0}};
        var current_menu_lang = '{{request()->query('lang',app()->getLocale())}}';
        var preview_routes = {
            preview: '{{route('template.admin.live.preview')}}'
        }
    </script>
    <script
        type="module"
        src="{{asset('module/template/preview/dist/js/app.js?_v='.config('app.asset_version'))}}"></script>
@endpush
