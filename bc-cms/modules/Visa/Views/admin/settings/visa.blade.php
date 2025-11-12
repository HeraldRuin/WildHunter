<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{__("Page Search")}}</h3>
        <p class="form-group-desc">{{__('Config page search of your website')}}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-title"><strong>{{__("General Options")}}</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="" >{{__("Title Page")}}</label>
                    <div class="form-controls">
                        <input type="text" name="visa_page_search_title" value="{{setting_item_with_lang('visa_page_search_title',request()->query('lang'))}}" class="form-control">
                    </div>
                </div>
                @if(is_default_lang())
                    <div class="form-group">
                        <label class="" >{{__("Banner Page")}}</label>
                        <div class="form-controls form-group-image">
                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('visa_page_search_banner',$settings['visa_page_search_banner'] ?? "") !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="" >{{__("Layout Search")}}</label>
                        <div class="form-controls">
                            <select name="visa_layout_search" class="form-control" >
                                @foreach(config('visa.layouts',['normal'=>__("Normal Layout"),'map'=>__("Map Layout")]) as $id=>$name))
                                    <option value="{{$id}}" {{ setting_item('visa_layout_search','normal') == $id ? 'selected' : ''  }}>{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <?php do_action(\Modules\Visa\Hook::VISA_SETTING_AFTER_LAYOUT_SEARCH) ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="" >{{__("Limit item per Page")}}</label>
                                <div class="form-controls">
                                    <input type="number" min="1" name="visa_page_limit_item" placeholder="{{ __("Default: 9") }}" value="{{setting_item_with_lang('visa_page_limit_item',request()->query('lang'), 9)}}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @include('Visa::admin.settings.form-search')
        <div class="panel">
            <div class="panel-title"><strong>{{__("SEO Options")}}</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#seo_1">{{__("General Options")}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#seo_2">{{__("Share Facebook")}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#seo_3">{{__("Share Twitter")}}</a>
                        </li>
                    </ul>
                    <div class="tab-content" >
                        <div class="tab-pane active" id="seo_1">
                            <div class="form-group" >
                                <label class="control-label">{{__("Seo Title")}}</label>
                                <input type="text" name="visa_page_list_seo_title" class="form-control" placeholder="{{__("Enter title...")}}" value="{{ setting_item_with_lang('visa_page_list_seo_title',request()->query('lang'))}}">
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__("Seo Description")}}</label>
                                <input type="text" name="visa_page_list_seo_desc" class="form-control" placeholder="{{__("Enter description...")}}" value="{{setting_item_with_lang('visa_page_list_seo_desc',request()->query('lang'))}}">
                            </div>
                            @if(is_default_lang())
                                <div class="form-group form-group-image">
                                    <label class="control-label">{{__("Featured Image")}}</label>
                                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('visa_page_list_seo_image', $settings['visa_page_list_seo_image'] ?? "" ) !!}
                                </div>
                            @endif
                        </div>
                        @php
                            $seo_share = json_decode(setting_item_with_lang('visa_page_list_seo_share',request()->query('lang'),'[]'),true);
                        @endphp
                        <div class="tab-pane" id="seo_2">
                            <div class="form-group">
                                <label class="control-label">{{__("Facebook Title")}}</label>
                                <input type="text" name="visa_page_list_seo_share[facebook][title]" class="form-control" placeholder="{{__("Enter title...")}}" value="{{$seo_share['facebook']['title'] ?? "" }}">
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__("Facebook Description")}}</label>
                                <input type="text" name="visa_page_list_seo_share[facebook][desc]" class="form-control" placeholder="{{__("Enter description...")}}" value="{{$seo_share['facebook']['desc'] ?? "" }}">
                            </div>
                            @if(is_default_lang())
                                <div class="form-group form-group-image">
                                    <label class="control-label">{{__("Facebook Image")}}</label>
                                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('visa_page_list_seo_share[facebook][image]',$seo_share['facebook']['image'] ?? "" ) !!}
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane" id="seo_3">
                            <div class="form-group">
                                <label class="control-label">{{__("Twitter Title")}}</label>
                                <input type="text" name="visa_page_list_seo_share[twitter][title]" class="form-control" placeholder="{{__("Enter title...")}}" value="{{$seo_share['twitter']['title'] ?? "" }}">
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__("Twitter Description")}}</label>
                                <input type="text" name="visa_page_list_seo_share[twitter][desc]" class="form-control" placeholder="{{__("Enter description...")}}" value="{{$seo_share['twitter']['desc'] ?? "" }}">
                            </div>
                            @if(is_default_lang())
                                <div class="form-group form-group-image">
                                    <label class="control-label">{{__("Twitter Image")}}</label>
                                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('visa_page_list_seo_share[twitter][image]', $seo_share['twitter']['image'] ?? "" ) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(is_default_lang())
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <h3 class="form-group-title">{{__("Review Options")}}</h3>
            <p class="form-group-desc">{{__('Config review for visa')}}</p>
        </div>
        <div class="col-sm-8">
            <div class="panel"> 
                <div class="panel-body">
                    <div class="form-group">
                        <label class="" >{{__("Enable review system for Visa?")}}</label>
                        <div class="form-controls">
                            <label><input type="checkbox" name="visa_enable_review" value="1" @if(!empty($settings['visa_enable_review'])) checked @endif /> {{__("Yes, please enable it")}} </label>
                            <br>
                            <small class="form-text text-muted">{{__("Turn on the mode for reviewing visa")}}</small>
                        </div>
                    </div>
                    <div class="form-group" data-condition="visa_enable_review:is(1)">
                        <label class="" >{{__("Customer must book a visa before writing a review?")}}</label>
                        <div class="form-controls">
                            <label><input type="checkbox" name="visa_enable_review_after_booking" value="1"  @if(!empty($settings['visa_enable_review_after_booking'])) checked @endif /> {{__("Yes please")}} </label>
                            <br>
                            <small class="form-text text-muted">{{__("ON: Only post a review after booking - Off: Post review without booking")}}</small>
                        </div>
                    </div>
                    <div class="form-group" data-condition="visa_enable_review:is(1),visa_enable_review_after_booking:is(1)">
                        <label>{{__("Allow review after making Completed Booking?")}}</label>
                        <div class="form-controls">
                            @php
                                $status = config('booking.statuses');
                                $settings_status = !empty($settings['visa_allow_review_after_making_completed_booking']) ? json_decode($settings['visa_allow_review_after_making_completed_booking']) : [];
                            @endphp
                            <div class="row">
                                @foreach($status as $item)
                                    <div class="col-md-4">
                                        <label><input type="checkbox" name="visa_allow_review_after_making_completed_booking[]" @if(in_array($item,$settings_status)) checked @endif value="{{$item}}"  /> {{booking_status_to_text($item)}} </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">{{__("Pick to the Booking Status, that allows reviews after booking")}}</small>
                            <small class="form-text text-muted">{{__("Leave blank if you allow writing the review with all booking status")}}</small>
                        </div>
                    </div>
                    <div class="form-group" data-condition="visa_enable_review:is(1)">
                        <label class="" >{{__("Review must be approval by admin")}}</label>
                        <div class="form-controls">
                            <label><input type="checkbox" name="visa_review_approved" value="1"  @if(!empty($settings['visa_review_approved'])) checked @endif /> {{__("Yes please")}} </label>
                            <br>
                            <small class="form-text text-muted">{{__("ON: Review must be approved by admin - OFF: Review is automatically approved")}}</small>
                        </div>
                    </div>
                    <div class="form-group" data-condition="visa_enable_review:is(1)">
                        <label class="" >{{__("Review number per page")}}</label>
                        <div class="form-controls">
                            <input type="number" class="form-control" name="visa_review_number_per_page" value="{{ $settings['visa_review_number_per_page'] ?? 5 }}" />
                            <small class="form-text text-muted">{{__("Break comments into pages")}}</small>
                        </div>
                    </div>
                    <div class="form-group" data-condition="visa_enable_review:is(1)">
                        <label class="" >{{__("Review criteria")}}</label>
                        <div class="form-controls">
                            <div class="form-group-item">
                                <div class="g-items-header">
                                    <div class="row">
                                        <div class="col-md-5">{{__("Title")}}</div>
                                        <div class="col-md-1"></div>
                                    </div>
                                </div>
                                <div class="g-items">
                                    <?php
                                    if(!empty($settings['visa_review_stats'])){
                                    $social_share = json_decode($settings['visa_review_stats']);
                                    ?>
                                    @foreach($social_share as $key=>$item)
                                        <div class="item" data-number="{{$key}}">
                                            <div class="row">
                                                <div class="col-md-11">
                                                    <input type="text" name="visa_review_stats[{{$key}}][title]" class="form-control" value="{{$item->title}}" placeholder="{{__('Eg: Service')}}">
                                                </div>
                                                <div class="col-md-1">
                                                    <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <?php } ?>
                                </div>
                                <div class="text-right">
                                    <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> {{__('Add item')}}</span>
                                </div>
                                <div class="g-more hide">
                                    <div class="item" data-number="__number__">
                                        <div class="row">
                                            <div class="col-md-11">
                                                <input type="text" __name__="visa_review_stats[__number__][title]" class="form-control" value="" placeholder="{{__('Eg: Service')}}">
                                            </div>
                                            <div class="col-md-1">
                                                <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@if(is_default_lang())
<hr>

<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{__("Booking Deposit")}}</h3>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-title"><strong>{{__("Booking Deposit Options")}}</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="form-controls">
                    <label><input type="checkbox" name="visa_deposit_enable" value="1" @if(setting_item('visa_deposit_enable')) checked @endif > {{__('Yes, please enable it')}}</label>
                    </div>
                </div>
                <div class="form-group" data-condition="visa_deposit_enable:is(1)">
                    <label >{{__('Deposit Amount')}}</label>
                    <div class="form-controls">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="number" name="visa_deposit_amount" class="form-control" step="0.1" value="{{old('visa_deposit_amount',setting_item('visa_deposit_amount'))}}" >
                                    <select name="visa_deposit_type"  class="form-control">
                                        <option value="fixed">{{__("Fixed")}}</option>
                                        <option @if(old('visa_deposit_type',setting_item('visa_deposit_type')) == 'percent') selected @endif value="percent">{{__("Percent")}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group" data-condition="visa_deposit_enable:is(1)">
                    <label class="" >{{__("Deposit Fomular")}}</label>
                    <div class="form-controls">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="visa_deposit_fomular" class="form-control" >
                                    <option value="default" {{($settings['visa_deposit_fomular'] ?? '') == 'default' ? 'selected' : ''  }}>{{__('Default')}}</option>
                                    <option value="deposit_and_fee" {{ ($settings['visa_deposit_fomular'] ?? '') == 'deposit_and_fee' ? 'selected' : ''  }}>{{__("Deposit amount + Buyer free")}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{__("Disable visa module?")}}</h3>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-title"><strong>{{__("Disable visa module")}}</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="form-controls">
                    <label><input type="checkbox" name="visa_disable" value="1" @if(setting_item('visa_disable')) checked @endif > {{__('Yes, please disable it')}}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

