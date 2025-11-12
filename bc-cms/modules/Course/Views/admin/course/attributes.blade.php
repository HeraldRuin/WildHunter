<div class="panel">
    <div class="panel-title"><strong>{{__('Category')}}</strong></div>
    <div class="panel-body">
        <select name="cat_id" class="form-control">
            <option value="">{{__("-- Please Select --")}}</option>
            <?php
            $traverse = function ($categories, $prefix = '') use (&$traverse, $row) {
                foreach ($categories as $category) {
                    $selected = '';
                    if ($row->cat_id == $category->id)
                        $selected = 'selected';
                    printf("<option value='%s' %s>%s</option>", $category->id, $selected, $prefix . ' ' . $category->name);
                    $traverse($category->children, $prefix . '-');
                }
            };
            $traverse($category_tree);
            ?>
        </select>
    </div>
</div>
<div class="panel">
    <div class="panel-title"><strong>{{__('Skill Level')}}</strong></div>
    <div class="panel-body">
        <select name="level_id" class="form-control">
            <option value="">{{__("-- Please Select --")}}</option>
            @foreach ($levels as $level)
                <option value="{{ $level->id }}" @if($row->level_id == $level->id) selected @endif>{{ $level->translate()->name }}</option>
            @endforeach
        </select>
    </div>
</div>
@foreach ($attributes as $attribute)
    <div class="panel">
        <div class="panel-title"><strong>{{__('Attribute: :name',['name'=>$attribute->name])}}</strong></div>
        <div class="panel-body">
            <div class="terms-scrollable">
                @foreach($attribute->terms as $term)
                    <label class="term-item">
                        <input @if(!empty($selected_terms) and $selected_terms->contains($term->id)) checked @endif type="checkbox" name="terms[]" value="{{$term->id}}">
                        <span class="term-name">{{$term->name}}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
