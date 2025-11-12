<label for="data_{{ $field['id'] }}">{{ $field['label'] }}
    @if(strpos($field['rules'], 'required') !== false)
        <span class="text-danger">*</span>
    @endif
</label>