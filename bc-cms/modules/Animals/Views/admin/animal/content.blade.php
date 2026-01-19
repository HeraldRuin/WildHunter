<div class="panel">
    <div class="panel-title"><strong>{{__("Animal Content")}}</strong></div>
    <div class="panel-body">
        <div class="form-group magic-field" data-id="title" data-type="title">
            <label class="control-label">{{__("Title")}}</label>
            <input type="text" value="{{$translation->title}}" placeholder="{{__("Title")}}" name="title" class="form-control">
        </div>

        <div class="form-group-item">
            <div class="g-more hide">
                <div class="item" data-number="__number__">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" __name__="faqs[__number__][title]" class="form-control" placeholder="{{__('Eg: Can I bring my pet?')}}">
                        </div>
                        <div class="col-md-6">
                            <textarea __name__="faqs[__number__][content]" class="form-control" placeholder=""></textarea>
                        </div>
                        <div class="col-md-1">
                            <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(is_default_lang())
            <div class="form-group">
                <label class="control-label">{{__("Banner Image")}}</label>
                <div class="form-group-image">
                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('banner_image_id',$row->banner_image_id) !!}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__("Gallery")}}</label>
                {!! \Modules\Media\Helpers\FileHelper::fieldGalleryUpload('gallery',$row->gallery) !!}
            </div>
        @endif
    </div>
</div>

<div class="panel">
    <div class="panel-title"><strong>{{__("Trophy Type")}}</strong></div>
    <div class="panel-body">
        @if(is_default_lang())
            <div class="form-group-item">
                <label class="control-label">{{__("Trophy Types")}}</label>
                <div class="g-items">
                    @if(!empty($trophies) && $trophies->count() > 0)
                        @foreach($trophies as $key=>$trophy)
                            <div class="item" data-number="{{$key}}">
                                <div class="row">
                                    <div class="col-md-11">
                                        <input type="hidden" name="trophy_types[{{$key}}][id]" value="{{$trophy->id}}">
                                        <input type="text" name="trophy_types[{{$key}}][type]" class="form-control" value="{{$trophy->type}}" placeholder="{{__('Enter trophy type')}}" required>
                                        @if($trophy->price)
                                            <small class="text-muted">{{__('Price set by base admin')}}: {{number_format($trophy->price, 2)}}</small>
                                        @else
                                            <small class="text-muted">{{__('Price will be set by base admin')}}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-1">
                                        <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="text-right">
                    <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> {{__('Add item')}}</span>
                </div>
                <div class="g-more hide">
                    <div class="item" data-number="__number__">
                        <div class="row">
                            <div class="col-md-11">
                                <input type="text" __name__="trophy_types[__number__][type]" class="form-control" placeholder="{{__('Enter trophy type')}}" required>
                                <small class="text-muted">{{__('Price will be set by base admin')}}</small>
                            </div>
                            <div class="col-md-1">
                                <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
