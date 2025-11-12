@if(!empty($attr))
    <input type="hidden" name="attr_id" value="{{$attr->id}}">
@endif
<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" value="{{$translation->name}}" placeholder="{{__("Term name")}}" name="name" class="form-control">
</div>
@if(is_default_lang())
<div class="form-group d-none">
    <label >{{__('Image')}}</label>
    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
</div>
{{-- <div class="form-group">
    <label>{{__("Class icon featured")}}</label>
    <input type="text" value="{{$row->icon}}" placeholder="{{__("Class icon featured")}}" name="icon" class="form-control">
</div> --}}
@endif
{{--<div class="form-group">--}}
    {{--<label class="control-label">{{__("Description")}}</label>--}}
    {{--<div class="">--}}
        {{--<textarea name="content" class="d-none has-ckeditor" cols="30" rows="10">{{$translation->content}}</textarea>--}}
    {{--</div>--}}
{{--</div>--}}