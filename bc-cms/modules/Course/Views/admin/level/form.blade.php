<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" value="{{$translation->name}}" placeholder="{{__("Level name")}}" name="name" class="form-control">
</div>
@if(is_default_lang())
    <div class="form-group">
        <label>{{__("Status")}}</label>
        <select name="status" class="form-control">
            <option value="publish">{{__("Publish")}}</option>
            <option value="draft" @if($row->status=='draft') selected @endif>{{__("Draft")}}</option>
        </select>
    </div>

@endif
