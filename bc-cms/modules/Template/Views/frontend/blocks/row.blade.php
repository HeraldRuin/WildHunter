<div class="bc-section" style="{{empty($__isPreview) and !empty($css_code) ? $css_code : ''}}">
    <div class="@if($is_container === 'no') container-fluid @else container @endif">
        <div class="row ">
            @foreach($children as $nodeId => $child)
                @if(componentExists($child['type']))
                    @livewire($child['type'], $child['model'] ?? [], key($nodeId))
                @endif
            @endforeach

            @if(empty($children) && !empty($__isPreview))
                <div class="b-border-dashed b-border-2 b-rounded-2xl b-m-10 b-min-h-64 b-border-blue-500">

                </div>
            @endif
        </div>
    </div>
</div>
