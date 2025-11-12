<div class="panel">
    <div class="panel-title"><strong>{{__("Course Content")}}</strong></div>
    <div class="panel-body">
        <div class="form-group magic-field" data-id="title" data-type="title">
            <label class="control-label">{{__("Title")}}</label>
            <input type="text" value="{{$translation->title}}" placeholder="{{__("Title")}}" name="title" class="form-control">
        </div>
        <div class="form-group magic-field" data-id="content" data-type="content">
            <label class="control-label">{{__("Content")}}</label>
            <div class="">
                <textarea name="content" class="d-none has-ckeditor" id="content" cols="30" rows="10">{{$translation->content}}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Short Description")}}</label>
            <div class="">
                <textarea name="short_desc" class="form-control" cols="30" rows="3">{{$translation->short_desc}}</textarea>
            </div>
        </div>

        <div class="row">
            @if(is_default_lang())
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{__("Duration")}}</label>
                        <div class="input-group">
                            <input type="text" value="{{$row->duration}}" placeholder="{{__("Ex: 100")}}" name="duration" class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text small">{{ __("Minutes") }}</span>
                            </div>
                        </div>
                        <span><i class="small">{{ __("If left blank, the total time of the lectures will automatically be calculated") }}</i></span>
                    </div>
                </div>
            @endif
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{__("Language")}}</label>
                    <input type="text" name="language" class="form-control" value="{{$row->language}}" placeholder="{{__("Language")}}">
                </div>
            </div>
        </div>

        @if(is_default_lang())
            <div class="form-group">
                <label class="control-label">{{__("Preview Video Url")}}</label>
                <input type="text" name="preview_url" class="form-control" value="{{$row->preview_url}}" placeholder="{{__("Video Url")}}">
            </div>
        @endif

    </div>
</div>
