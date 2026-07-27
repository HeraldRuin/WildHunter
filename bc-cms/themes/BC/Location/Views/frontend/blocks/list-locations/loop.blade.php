@php
    /**
    * @var $row \Modules\Location\Models\Location
    * @var $to_location_detail bool
    * @var $service_type string
    */
    $translation = $row->translate();
    $link_location = false;
    if(is_string($service_type)){
        $link_location = $row->getLinkForPageSearch($service_type);
    }
    if(is_array($service_type) and count($service_type) >= 1){
        $type_for_link = in_array('hotel', $service_type, true) ? 'hotel' : ($service_type[0] ?? '');
        $link_location = $row->getLinkForPageSearch($type_for_link);
    }
    if($to_location_detail){
        $link_location = $row->getDetailUrl();
    }
@endphp
<div class="destination-item @if(!$row->image_id) no-image  @endif">
    <div class="image" @if($row->image_id) style="background: url({{$row->getImageUrl()}})" @endif >
        <div class="effect"></div>
        <div class="content">
            <h4 class="title">
                @if(!empty($link_location))
                    <a href="{{$link_location}}">{{$translation->name}}</a>
                @else
                    {{$translation->name}}
                @endif
            </h4>
            @if( !empty($layout) and ($layout == "style_1" or $layout == "style_3" or $layout == "style_4"))
                @if(is_array($service_type))
                    <div class="desc">
                        @foreach($service_type as $type)
                            @php $count = $row->getDisplayNumberServiceInLocation($type);
                               $count = str_replace(['отель', 'отеля', 'отелей'], 'базы', $count);
                            @endphp

                            @if(!empty($count))
                                <span>{{$count}}</span>
                            @endif
                        @endforeach
                    </div>
                @else
                    @if(!empty($text_service = $row->getDisplayNumberServiceInLocation($service_type)))
                        <div class="desc">{{$text_service}}</div>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>
