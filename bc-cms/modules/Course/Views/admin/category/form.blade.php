<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" value="{{$translation->name}}" placeholder="{{__("Category name")}}" name="name" class="form-control">
</div>
@if(is_default_lang())
    <div class="form-group">
        <label>{{__("Parent")}}</label>
        <select name="parent_id" class="form-control">
            <option value="">{{__("-- Please Select --")}}</option>
            <?php
            $traverse = function ($categories, $prefix = '') use (&$traverse, $row) {
                foreach ($categories as $category) {
                    if ($category->id == $row->id) {
                        continue;
                    }
                    $selected = '';
                    if ($row->parent_id == $category->id)
                        $selected = 'selected';
                    printf("<option value='%s' %s>%s</option>", $category->id, $selected, $prefix . ' ' . $category->name);
                    $traverse($category->children, $prefix . '-');
                }
            };
            $traverse($parents);
            ?>
        </select>
    </div>
    <div class="form-group">
        <label class="control-label">{{__("Image")}}</label>
        <div class="">        
            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
        </div>
    </div>
    
    <div class="form-group">
        <label>{{__("Status")}}</label>
        <select name="status" class="form-control">
            <option value="publish">{{__("Publish")}}</option>
            <option value="draft" @if($row->status=='draft') selected @endif>{{__("Draft")}}</option>
        </select>
    </div>

@endif