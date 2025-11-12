<div class="filter-item">
    <div class="form-group">
        <i class="field-icon icofont-paperclip"></i>
        <div class="form-content" wire:ignore>
            <label>{{ $field['title'] ?? "" }}</label>
            <?php 
            $old = !empty($visa_type) ?? '';
            $list_cat_json = [
                [
                    'id' => '',
                    'title' => __('Any Visa Type'),
                ],
            ];
            $visa_types = \Modules\Visa\Models\VisaType::search()->get();
            $selected = $old ? \Modules\Visa\Models\VisaType::find($old) : null;
            ?>
            @foreach($visa_types as $visa_type)
                @php $translate = $visa_type->translate();
                $list_cat_json[] = [
                    'id' => $visa_type->id,
                    'title' => $translate->name,
                ];
                @endphp
            @endforeach
            <div class="smart-search">
                <input type="text" class="smart-select parent_text form-control" readonly placeholder="{{__("All Visa Type")}}" value="{{ $selected ? $selected->name ?? '' :'' }}" data-default="{{ json_encode($list_cat_json) }}">
                <input type="hidden" class="child_id" name="visa_type" value="{{$old}}">
            </div>
        </div>
    </div>
</div>