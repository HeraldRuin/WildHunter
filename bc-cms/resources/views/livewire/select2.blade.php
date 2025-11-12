<div class="select2-container {{ $class }}" >
    <div wire:ignore>
        <select id="{{$id}}" name="{{$name}}" style="width: 100%">
            @if(!empty($placeholder))
                <option value="">{{$placeholder}}</option>
            @endif
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" @if ($selected == $value) selected @endif>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    <input type="hidden" wire:model="selected" id="{{$id}}-hidden">
</div>

@script
<script>
    let select = $('#{{$id}}');

    select.select2({
        allowClear: true,
    });

    // Set initial value from Livewire
    select.val($wire.selected).trigger('change');

    // Sync Select2 -> Livewire
    select.on('change', function () {
        let value = $(this).val();
        $wire.selected = value;
        updateWrapperClass($wire.selected);
    });

    // Sync Livewire -> Select2 (optional: when model changes from outside)
    Livewire.on('refreshSelect2', () => {
        select.val($wire.selected).trigger('change');
    });

    
    // Function to update wrapper class based on selected value
    function updateWrapperClass(selected) {
        let wrapper = select.closest('.select2-container');
        if (selected.length > 0) {
            wrapper.addClass('is-selected');
        } else {
            wrapper.removeClass('is-selected');
        }
    }

    // Cleanup on component destruction
    document.addEventListener('livewire:navigating', () => {
        select.select2('destroy');
    });
</script>
@endscript
