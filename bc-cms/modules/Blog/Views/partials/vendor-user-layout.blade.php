@push('css')
    <style>
        /* Как у Animals/Hotel: контент уже, сайдбар не трогаем.
           overflow только у колонки контента — иначе -83px у .row-col обрезает сайдбар. */
        .user-page.blog-vendor-page .bc_user_profile > .container-fluid > .row-eq-height > .col-md-9 {
            min-width: 0;
            overflow-x: hidden;
        }
    </style>
@endpush

@push('js')
    {{-- Тот же Vue 3 медиа-браузер, что в админке: Vue 2-версия на странице редактора не рисует файлы и папки --}}
    <script type="module" src="{{ asset('themes/admin/dist/js/browser.js?_ver=' . config('app.asset_version')) }}"></script>
@endpush
