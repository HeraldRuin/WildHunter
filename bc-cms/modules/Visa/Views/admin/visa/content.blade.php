<div class="panel">
    <div class="panel-title"><strong>{{__("Visa Content")}}</strong></div>
    <div class="panel-body">
        <div class="form-group magic-field" data-id="title" data-type="title">
            <label class="control-label">{{__("Title")}}</label>
            <input required type="text" value="{{old('title',$translation->title)}}" placeholder="{{__("Title")}}" name="title" class="form-control">
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Slug (Optional, auto generate)")}}</label>
            <input type="text" value="{{old('slug',$row->slug)}}" placeholder="{{__("Slug")}}" name="slug" class="form-control">
        </div>
        @if(is_default_lang())
        <div class="form-group">
            <label class="control-label">{{__("Country")}}</label>
            <select wire:model="to_country" name="to_country" class="form-control">
                @foreach (get_country_lists() as $key => $value)
                    <option @if(old('to_country',$row->to_country) == $key) selected @endif value="{{$key}}" >{{$value}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Code")}}</label>
            <input required type="text" value="{{old('code',$row->code)}}" placeholder="{{__("Code (Unique)")}}" name="code" class="form-control">
            <p class="text-muted">{{__("Alphanumeric, dash, and underscore are allowed")}}</p>
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Visa Type")}}</label>
            <select wire:model="type_id" name="type_id" class="form-control">
                @foreach ($types as $key => $value)
                    <option @if(old('type_id',$row->type_id) == $value->id) selected @endif value="{{$value->id}}" >{{$value->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Price")}}</label>
            <input type="text" value="{{old('price',$row->price)}}" placeholder="{{__("Price")}}" name="price" class="form-control">
        </div>

        <div class="form-group">
            <label class="control-label">{{__("Original Price (Before Discount, optional)")}}</label>
            <input type="text" value="{{old('original_price',$row->original_price)}}" placeholder="{{__("Original Price")}}" name="original_price" class="form-control" wire:ignore>
        </div>

        <div class="form-group">
            <label class="control-label">{{__("Processing Days")}}</label>
            <input type="text" value="{{old('processing_days',$row->processing_days)}}" placeholder="{{__("Processing Days")}}" name="processing_days" class="form-control">
        </div>

        <div class="form-group">
            <label class="control-label">{{__("Max Stay Days")}}</label>
            <input type="text" value="{{old('max_stay_days',$row->max_stay_days)}}" placeholder="{{__("Max Stay Days")}}" name="max_stay_days" class="form-control">
        </div>

        <div class="form-group">
            <label class="control-label">{{__("Multiple Entry")}}</label>
            <input type="text" value="{{old('multiple_entry',$row->multiple_entry)}}" placeholder="{{__("Multiple Entry")}}" name="multiple_entry" class="form-control">
        </div>
        @endif

        
        <div class="form-group magic-field" data-id="content" data-type="content">
            <label class="control-label">{{__("Content")}}</label>
            <div class="" wire:ignore>
                <textarea name="content" class="d-none has-ckeditor" id="content" cols="30" rows="10">{{old('content',$translation->content)}}</textarea>
            </div>
        </div>
    </div>
</div>