<div class="item-list">
    @if($animal->discount_percent)
        <div class="sale_info">{{$animal->discount_percent}}</div>
    @endif
    <div class="row">
        <div class="col-md-3">
            @if($animal->is_featured == "1")
                <div class="featured">
                    {{__("Featured")}}
                </div>
            @endif
            <div>

                @if(!empty($animal->image_url))
                    <div class="thumb-image">
                    <a href="{{$animal->getDetailUrl()}}" target="_blank">
                    </a>
                    </div>
                    @else
                        <div style="display:flex;align-items:center;justify-content:center;min-height:150px;width:100%;border:1px solid #ccc;">
                            {{ __("No Image") }}
                        </div>
                    @endif

        </div>

            <div class="thumb-image">
                <a href="{{$animal->getDetailUrl()}}" target="_blank">
                    @if(!empty($animal->image_url))
                        <img src="{{$animal->image_url}}" class="img-responsive" alt="">
                    @else
                        <div style="display:flex;align-items:center;justify-content:center;min-height:150px;width:100%;border:1px solid #ccc;">
                            {{ __("No Image") }}
                        </div>
                    @endif
                </a>

                {{--                <div class="service-wishlist {{$animal->isWishList()}}" data-id="{{$animal->id}}" data-type="{{$animal->type}}">--}}
{{--                    <i class="fa fa-heart"></i>--}}
{{--                </div>--}}
            </div>
        </div>
        <div class="col-md-9">
            <div class="item-title">
                <a href="{{$animal->getDetailUrl()}}" target="_blank">
                    {{$animal->title}}
                </a>
            </div>
            <div class="location">
                @if(!empty($animal->location->name))
                    <i class="icofont-paper-plane"></i>
                    {{__("Location")}}: {{$animal->location->name ?? ''}}
                @endif
            </div>
            <div class="location">
                <i class="icofont-money"></i>
                {{__("Price")}}: <span class="sale-price">{{ $animal->display_sale_price_admin }}</span> <span class="price">{{ $animal->display_price_admin }}</span>
            </div>
            <div class="location">
                <i class="icofont-ui-settings"></i>
                {{__("Status")}}: <span class="badge badge-{{ $animal->status }}">{{ $animal->status_text }}</span>
            </div>
            <div class="location">
                <i class="icofont-wall-clock"></i>
                {{__("Last Updated")}}: {{ display_datetime($animal->updated_at ?? $animal->created_at) }}
            </div>
            <div class="control-action">
                <a href="{{$animal->getDetailUrl()}}" target="_blank" class="btn btn-info">{{__("View")}}</a>
                @if(!empty($recovery))
                    <a href="{{ route("animal.vendor.restore",[$animal->id]) }}" class="btn btn-recovery btn-primary" data-confirm="{{__('"Do you want to recovery?"')}}">{{__("Recovery")}}</a>
                    @if(Auth::user()->hasPermission('animal_delete'))
                        <a href="{{ route("animal.vendor.delete",['id'=>$animal->id,'permanently_delete'=>1]) }}" class="btn btn-danger" data-confirm="{{__('"Do you want to permanently delete?"')}}">{{__("Del")}}</a>
                    @endif
                @else

                    @if(is_null($animal->animal_status))
                        <a href="{{ route("animal.vendor.bulk_edit",[$animal->id,'action' => "add"]) }}" class="btn btn-warning">{{__("Attach to base")}}</a>
                    @endif
                    @if($animal->animal_status == 'available')
                        <a href="{{ route("animal.vendor.bulk_edit",[$animal->id,'action' => "delete"]) }}" class="btn btn-success">{{__("Detach to base")}}</a>
                    @endif

                    {{--                    @if(Auth::user()->hasPermission('animal_update'))--}}
{{--                        <a href="{{ route("animal.vendor.edit",[$row->id]) }}" class="btn btn-warning">{{__("Edit")}}</a>--}}
{{--                    @endif--}}
{{--                    @if(Auth::user()->hasPermission('animal_delete'))--}}
{{--                        <a href="{{ route("animal.vendor.delete",[$row->id]) }}" class="btn btn-danger" data-confirm="{{__('"Do you want to delete?"')}}">{{__("Del")}}</a>--}}
{{--                    @endif--}}
{{--                    @if($row->status == 'publish')--}}
{{--                        <a href="{{ route("animal.vendor.bulk_edit",[$row->id,'action' => "make-hide"]) }}" class="btn btn-secondary">{{__("Make hide")}}</a>--}}
{{--                    @endif--}}
{{--                    @if($row->status == 'draft')--}}
{{--                        <a href="{{ route("animal.vendor.bulk_edit",[$row->id,'action' => "make-publish"]) }}" class="btn btn-success">{{__("Make publish")}}</a>--}}
{{--                    @endif--}}
                @endif
            </div>
        </div>
    </div>
</div>
