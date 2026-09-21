@push('css')
    <link rel="stylesheet" href="{{ asset('themes/admin/dist/css/blogEditor.css?_v='.config('app.asset_version')) }}">
    @if(!empty($user_editor))
        <style>
            /*
             * user.css: .container_col { width: 130% } + .col-md-9 { flex-shrink: 0; 75% }
             * → контент шире окна, правая колонка обрезается.
             * На странице редактора — нормальная сетка 100vw: сайдбар + остаток под редактор.
             */
            .user-page.blog-editor-page .bc_user_profile > .container-fluid.container_col {
                width: 100% !important;
                max-width: 100vw !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .user-page.blog-editor-page .bc_user_profile > .container-fluid > .row.row-col {
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
                max-width: 100vw !important;
                flex-wrap: nowrap;
            }

            .user-page.blog-editor-page .bc_user_profile > .container-fluid > .row-eq-height > .col-md-3.sidebar-col {
                flex: 0 0 220px !important;
                width: 220px !important;
                max-width: 220px !important;
            }

            .user-page.blog-editor-page .bc_user_profile > .container-fluid > .row-eq-height > .col-md-9 {
                flex: 1 1 0 !important;
                width: auto !important;
                max-width: calc(100vw - 220px) !important;
                min-width: 0 !important;
                padding-left: 0;
                padding-right: 0;
            }

            .user-page.blog-editor-page .user-form-settings {
                padding: 0;
                min-width: 0;
                max-width: 100%;
                overflow: hidden;
            }

            .user-page.blog-editor-page .blog-editor-root,
            .user-page.blog-editor-page .blog-editor-root #blog-editor,
            .user-page.blog-editor-page .blog-editor-root .blog-editor-workspace {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                box-sizing: border-box;
            }

            .user-page.blog-editor-page .blog-editor-root #blog-editor {
                display: flex;
                flex-direction: column;
                height: calc(100vh - 60px);
                min-height: 600px;
                overflow: hidden;
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
                width: auto !important;
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

            .user-page.blog-editor-page .blog-editor-root .blog-preview-container {
                max-width: 100%;
                width: 100%;
                margin: 0;
                padding: 16px;
                box-sizing: border-box;
            }

            .user-page.blog-editor-page .blog-editor-root .blog-topbar > .d-flex:first-child {
                flex: 1 1 0;
                min-width: 0;
            }

            /* Синий сайдбар профиля: контент на всю высоту, выход/главная внизу */
            .user-page.blog-editor-page .bc_user_profile > .container-fluid > .row-eq-height {
                align-items: stretch;
            }

            .user-page.blog-editor-page .bc_user_profile .sidebar-user {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                height: 100%;
                box-sizing: border-box;
            }

            .user-page.blog-editor-page .bc_user_profile .sidebar-menu {
                flex: 1 1 0;
                min-height: 0;
                overflow-y: auto;
            }

            .user-page.blog-editor-page .bc_user_profile .sidebar-user > .logout:first-of-type {
                margin-top: auto;
                flex-shrink: 0;
            }

            .user-page.blog-editor-page .bc_user_profile .sidebar-user > .logout {
                flex-shrink: 0;
            }
        </style>
    @endif
@endpush

@push('js')
    <script src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script type="module" src="{{ asset('themes/admin/dist/js/blogEditor.js?_v='.config('app.asset_version')) }}"></script>
@endpush
