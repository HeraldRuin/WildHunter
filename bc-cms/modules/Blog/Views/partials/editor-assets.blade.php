@include('Blog::partials.vendor-user-layout')

@push('css')
    <link rel="stylesheet" href="{{ asset('themes/admin/dist/css/blogEditor.css?_v='.filemtime(public_path('themes/admin/dist/css/blogEditor.css'))) }}">
    <style>
        .blog-editor-root .blog-left-zone .blog-fixed-sections {
            flex: 0 0 auto;
            border-bottom: 1px solid #e7e6e6;
        }
    </style>
    @if(!empty($user_editor))
        <style>
            .user-page.blog-editor-page .bc_user_profile > .container-fluid > .row-eq-height > .col-md-9 {
                display: flex;
                flex-direction: column;
                align-self: stretch;
            }

            .user-page.blog-editor-page .user-form-settings {
                padding: 0;
                display: flex;
                flex-direction: column;
                flex: 1 1 auto;
                min-height: 0;
            }

            .user-page.blog-editor-page .blog-editor-root.list-animal-width {
                padding-left: 0;
                padding-right: 0;
                overflow-x: hidden;
                display: flex;
                flex-direction: column;
                flex: 1 1 auto;
                min-height: 0;
            }

            .user-page.blog-editor-page .blog-editor-root #blog-editor {
                display: flex;
                flex-direction: column;
                flex: 0 0 auto;
                height: 100vh;
                max-height: 100vh;
                min-height: 0;
                overflow: hidden;
                width: 100%;
                max-width: 100%;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-editor-workspace {
                flex: 1 1 auto;
                min-height: 0;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-left-zone {
                flex: 0 0 170px !important;
                width: 170px !important;
                max-width: 170px !important;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-right-zone {
                flex: 0 0 220px !important;
                width: 220px !important;
                max-width: 220px !important;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-editor-main {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                min-height: 0 !important;
                overflow: hidden;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-content-zone {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                width: 100% !important;
                padding: 8px;
                overflow: auto;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-block-settings-zone {
                max-height: min(38vh, 280px);
            }

            .user-page.blog-editor-page .blog-editor-root .blog-block-settings-zone.is-faq {
                max-height: min(60vh, 560px);
                margin-top: auto;
                flex: 0 0 auto;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-preview-container {
                max-width: 100%;
                width: 100%;
                margin: 0;
                padding: 16px;
                box-sizing: border-box;
                overflow-x: hidden;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-preview-text,
            .user-page.blog-editor-page .blog-editor-root .blog-preview-text * {
                max-width: 100%;
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-block-settings-zone,
            .user-page.blog-editor-page .blog-editor-root .blog-text-editor-wrap,
            .user-page.blog-editor-page .blog-editor-root .blog-text-editor,
            .user-page.blog-editor-page .blog-editor-root .tox,
            .user-page.blog-editor-page .blog-editor-root .tox-tinymce {
                max-width: 100%;
                min-width: 0;
                box-sizing: border-box;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-columns {
                display: grid;
                gap: 24px;
                align-items: start;
                width: 100%;
                max-width: 100%;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-columns-col {
                min-width: 0;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-topbar > .d-flex:first-child {
                flex: 1 1 0;
                min-width: 0;
            }
        </style>
    @endif
@endpush

@push('js')
    <script src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script type="module" src="{{ asset('themes/admin/dist/js/blogEditor.js?_v='.filemtime(public_path('themes/admin/dist/js/blogEditor.js'))) }}"></script>
@endpush
