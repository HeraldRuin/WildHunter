<div class="b-flex b-flex-col b-h-full" x-data="BC_Form_Edit">
    <div class="b-bg-white b-py-4 b-border-solid b-border-0 b-border-b b-border-gray-200">
        <div class="container-fluid">
            <div class="b-text-xl b-text-gray-800"><i class="fa fa-edit"></i> {{ __("Edit Form") }}</div>
        </div>
    </div>
    <div class="container-fluid b-flex-1">
        <div class="row">
            <div class="col-md-9 b-overflow-y-auto b-py-5">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10">
                        <div x-sort="handleSort" x-sort:group="fields" 
                        x-sort:config="{ handle:'.handle' }"
                        class="b-min-h-200 b-bg-white b-p-4 b-rounded" 
                        x-bind:class="fields.length == 0 ? 'b-border-dashed b-border-2 b-border-blue-500 b-rounded-md b-p-4' : ''">
                            @include('Form::admin.parts.field-preview')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 b-bg-white b-overflow-y-auto b-border-0 b-border-solid b-border-l b-border-l-gray-200 b-py-3">
                @include('Form::admin.parts.sidebar')
            </div>
        </div>
    </div>
</div>
@assets
    <script>
        const BC_ALL_FIELD_TYPES = @json($fieldTypes);
    </script>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/sort@3.x.x/dist/cdn.min.js"></script>
    <script src="{{asset('/module/form/admin/js/edit.js')}}"></script>
@endassets