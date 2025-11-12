<div class="bc-column @if(empty($__isPreview)) col-{{$size ?? 6}} @endif"
     style="{{(!empty($css_code) and empty($__isPreview)) ? $css_code : ''}}">
    @foreach($children as $nodeId => $child)
        @if(componentExists($child['type']))
            @livewire($child['type'], $child['model'] ?? [], key($nodeId))
        @endif
    @endforeach

    @if(empty($children) && !empty($__isPreview))
        <div class="b-border-dashed b-border-2 b-rounded-2xl b-m-10 b-min-h-64">

        </div>
    @endif
</div>
