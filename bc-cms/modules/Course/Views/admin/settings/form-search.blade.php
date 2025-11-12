@if(is_default_lang())
    @php $languages = \Modules\Language\Models\Language::getActive(); @endphp
    <hr>
    <div class="panel">
        <div class="panel-title"><strong>{{__("Form Search Fields")}}</strong></div>
        <div class="panel-body">
            <div class="form-group" >
                <label class="" >{{__("Search Criteria")}}</label>
                <div class="form-controls">
                    <div class="form-group-item">
                        <div class="g-items-header">
                            <div class="row">
                                <div class="col-md-7">{{__("Search Field")}}</div>
                                <div class="col-md-4">{{__("Order")}}</div>
                                <div class="col-md-1"></div>
                            </div>
                        </div>
                        <div class="g-items">
                            @php
                            $course_search_fields = setting_item_array('course_search_fields');
                            $types = [
                                'course_name'=>__("Course name"),
                                'category'=>__("Category"),
                                'ratings'=>__("Ratings"),
                                'instructors'=>__("Instructors"),
                                'price'=>__("Price"),
                                'level'=>__("Level"),
                                'attr'=>__("Attribute"),
                                'duration'=>__("Duration"),
                                'language'=>__("Language"),
                            ];
                            $attrs = \Modules\Core\Models\Attributes::where('service', 'course')->get();
                            @endphp
                            @foreach($course_search_fields as $key=>$item)
                                <div class="item" data-number="{{$key}}">
                                    <div class="row">
                                        <div class="col-md-7">
                                            @if(!empty($languages) && setting_item('site_enable_multi_lang') && setting_item('site_locale'))
                                                @foreach($languages as $language)
                                                    <?php $key_lang = setting_item('site_locale') != $language->locale ? "_".$language->locale : ""   ?>
                                                    <div class="g-lang">
                                                        <div class="title-lang">{{$language->name}}</div>
                                                        <input type="text" name="course_search_fields[{{$key}}][title{{$key_lang}}]" value="{{$item['title'.$key_lang] ?? ''}}" class="form-control">
                                                    </div>
                                                @endforeach
                                            @else
                                                <input type="text" name="course_search_fields[{{$key}}][title]" value="{{$item['title'] ?? ''}}" class="form-control">
                                            @endif
                                            <select name="course_search_fields[{{$key}}][field]" class="custom-select">
                                                <option value="">{{__("-- Select field type --")}}</option>
                                                @foreach($types as $type=>$name)
                                                    <option @if($item['field'] == $type) selected @endif value="{{$type}}">{{$name}}</option>
                                                @endforeach
                                            </select>
                                            <br>
                                            <select name="course_search_fields[{{$key}}][attr]" class="mt-2 custom-select">
                                                <option value="">{{__("-- Select Attribute --")}}</option>
                                                @foreach($attrs as $attr)
                                                    <option @if($item['attr'] ?? "" == $attr->id) selected @endif value="{{$attr->id}}">{{$attr->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" name="course_search_fields[{{$key}}][position]" min="0" value="{{$item['position'] ?? 0}}" class="form-control">
                                        </div>
                                        <div class="col-md-1">
                                            <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-right">
                            <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> {{__('Add item')}}</span>
                        </div>
                        <div class="g-more hide">
                            <div class="item" data-number="__number__">
                                <div class="row">
                                    <div class="col-md-7">
                                        @if(!empty($languages) && setting_item('site_enable_multi_lang') && setting_item('site_locale'))
                                            @foreach($languages as $language)
                                                <?php $key_lang = setting_item('site_locale') != $language->locale ? "_".$language->locale : ""   ?>
                                                <div class="g-lang">
                                                    <div class="title-lang">{{$language->name}}</div>
                                                    <input type="text" __name__="course_search_fields[__number__][title{{$key_lang}}]" class="form-control">
                                                </div>
                                            @endforeach
                                        @else
                                            <input type="text" __name__="course_search_fields[__number__][title]"  class="form-control">
                                        @endif
                                        <select __name__="course_search_fields[__number__][field]" class="custom-select">
                                            <option value="">{{__("-- Select field type --")}}</option>
                                            @foreach($types as $type=>$name)
                                                <option value="{{$type}}">{{$name}}</option>
                                            @endforeach
                                        </select>
                                        <br>
                                        <select __name__="course_search_fields[__number__][attr]" class="mt-2 custom-select">
                                            <option value="">{{__("-- Select Attribute --")}}</option>
                                            @foreach($attrs as $attr)
                                                <option value="{{$attr->id}}">{{$attr->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" __name__="course_search_fields[__number__][position]" min="0"  class="form-control">
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
@endif
