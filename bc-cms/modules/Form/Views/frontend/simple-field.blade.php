<div class="form-group col-{{ $field['col'] ?? 12 }}">
    <?php $inputClass = isset($errors) && $errors->has($field['id']) ? 'is-invalid' : '' ?>
    @if($field['type'] == 'text')
        @include('Form::frontend.field.label', ['field' => $field])
        <input 
        @if(strpos($field['rules'], 'required') !== false) required @endif 
        class="form-control {{ $inputClass }}" 
        id="data_{{ $field['id'] }}" 
        type="text" 
        wire:model="data.{{ $field['id'] }}" 
        placeholder="{{ $field['placeholder'] ?? '' }}"
        >
    @elseif($field['type'] == 'email')
        @include('Form::frontend.field.label', ['field' => $field])
        <input 
        @if(strpos($field['rules'], 'required') !== false) required @endif 
        class="form-control {{ $inputClass }}" 
        id="data_{{ $field['id'] }}" 
        type="email" 
        wire:model="data.{{ $field['id'] }}" 
        placeholder="{{ $field['placeholder'] ?? '' }}"
        >

    @elseif($field['type'] == 'number')
        @include('Form::frontend.field.label', ['field' => $field])
        <input 
        @if(strpos($field['rules'], 'required') !== false) required @endif 
        class="form-control {{ $inputClass }}" 
        id="data_{{ $field['id'] }}" 
        type="number" 
        wire:model="data.{{ $field['id'] }}" 
        placeholder="{{ $field['placeholder'] ?? '' }}"
        >

    @elseif($field['type'] == 'textarea')
        @include('Form::frontend.field.label', ['field' => $field])
        <textarea @if(strpos($field['rules'], 'required') !== false) required @endif class="form-control {{ $inputClass }}" id="data_{{ $field['id'] }}" wire:model="data.{{ $field['id'] }}" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
    @elseif($field['type'] == 'select')
        @include('Form::frontend.field.label', ['field' => $field])
        <select @if(strpos($field['rules'], 'required') !== false) required @endif class="form-control {{ $inputClass }}" id="data_{{ $field['id'] }}" wire:model="data.{{ $field['id'] }}">
            <?php
            if(!empty($field['data_source'])){
                $field['options'] = $this->getDataSource($field);
            }
            ?>
            @if(!empty($field['options']))
                <option value="">{{ __('--Select--') }}</option>
                @foreach($field['options'] as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            @endif
        </select>
    @elseif($field['type'] == 'radio')
        @include('Form::frontend.field.label', ['field' => $field])
        <div class="@error($field['id']) is-invalid @enderror">
            @foreach($field['options'] as $option)
                <input @if(strpos($field['rules'], 'required') !== false) required @endif type="radio" wire:model="data.{{ $field['id'] }}" id="{{ $field['id'] }}_{{ $option['value'] }}" name="{{ $field['id'] }}" value="{{ $option['value'] }}">
                <label for="{{ $field['id'] }}_{{ $option['value'] }}">{{ $option['label'] }}</label>
            @endforeach
        </div>
    @elseif($field['type'] == 'checkbox')
        @include('Form::frontend.field.label', ['field' => $field])
        <div class="@error($field['id']) is-invalid @enderror">
            @foreach($field['options'] as $option)
                <input type="checkbox" wire:model="data.{{ $field['id'] }}" value="{{ $option['value'] }}">
                <label for="{{ $option['value'] }}">{{ $option['label'] }}</label>
            @endforeach
        </div>
    @endif
    @if($field['type'] == 'date')
        @include('Form::frontend.field.label', ['field' => $field])
        <input @if(strpos($field['rules'], 'required') !== false) required @endif class="form-control {{ $inputClass }}" id="data_{{ $field['id'] }}" type="date" wire:model="data.{{ $field['id'] }}" placeholder="{{ $field['placeholder'] ?? '' }}">
    @endif

    @if($field['type'] == 'file_picker')
        @include('Form::frontend.field.label', ['field' => $field])
        @include('Form::frontend.field.file_picker', ['field' => $field])
    @endif

    @error($field['id'])
        <div class="invalid-feedback d-block"> {{ $message }} </div>
    @enderror
</div>