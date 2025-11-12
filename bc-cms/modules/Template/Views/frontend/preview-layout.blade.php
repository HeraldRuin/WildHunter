<div
    id="block-{{str_replace('.','',$__nodeId) ?? ''}}" class="live-block-preview selectable {{$wrapper_class  ?? ''}}"
    x-on:click.prevent.stop="window.LivePreview.selectItem('{{$__nodeId ?? ''}}')"
    style="{{$css_code ?? ''}}"

>
    <div class="block-info">
        <div>{{$this->getTitle()}}</div>
    </div>
    <div class="block-preview">
        @include($__view)
    </div>
</div>
