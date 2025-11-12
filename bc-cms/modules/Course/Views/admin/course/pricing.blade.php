<?php  $languages = \Modules\Language\Models\Language::getActive();  ?>
@if(is_default_lang())
<div class="panel">
    <div class="panel-title"><strong>{{__("Pricing")}}</strong></div>
    <div class="panel-body">
        @if(is_default_lang())
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="control-label">{{__("Price")}}</label>
                        <input type="number" required step="any" min="0" name="price" class="form-control" value="{{$row->price}}" placeholder="{{__("Price")}}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="control-label">{{__("Original Price")}}</label>
                        <input type="number" step="any" name="original_price" class="form-control" value="{{$row->original_price}}" placeholder="{{__("Original Price")}}">
                        <span><i class="small">{{__("Original should be greater than the price")}}</i></span>
                    </div>
                </div>

            </div>
        @endif
    </div>
</div>
@endif
