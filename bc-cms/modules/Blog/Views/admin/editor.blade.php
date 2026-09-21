@extends('Layout::admin.empty')

@section('content')
    @include('Blog::partials.editor-app', [
        'row' => $row,
        'index_route' => $index_route ?? route('blog.admin.index'),
        'save_url' => $save_url ?? route('blog.admin.store', ['id' => $row->id ?? 0]),
    ])
@endsection

@include('Blog::partials.editor-assets')
