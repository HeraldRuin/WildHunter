@extends('Layout::user')

@section('content')
    @include('Blog::partials.editor-app', [
        'row' => $row,
        'index_route' => $index_route,
        'save_url' => $save_url,
    ])
@endsection

@include('Blog::partials.editor-assets', ['user_editor' => true])
