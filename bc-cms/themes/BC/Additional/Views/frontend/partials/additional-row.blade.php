<tr data-id="{{ $additional->id }}">
    <td>
        <div class="d-flex gap-3">
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ $additional->name }}"
                   @if($additional->name === 'Питание') readonly @endif>

            @if($additional->name !== 'Питание')
                <input type="text"
                       name="count"
                       class="form-control"
                       value="{{ $additional->count ?? '' }}"
                       placeholder="кол-во">
            @endif
        </div>
    </td>

    <td>
        <input type="text"
               name="price"
               class="form-control price-input"
               value="{{ $additional->price }}"
               inputmode="decimal">
    </td>

    <td class="text-center" style="width: 260px">
        <button class="btn btn-success btn-sm save-period"
                data-id="{{ $additional->id }}">
            {{ __("Save") }}
        </button>

        <button class="btn btn-danger btn-sm remove-period"
                data-id="{{ $additional->id }}">
            {{ __("Delete") }}
        </button>
    </td>
</tr>

@push('js')
    <script>
        $(document).on('input', '.price-input', function () {
            console.log('INPUT WORKS', this.value);
            let value = this.value;

            value = value.replace(/[^0-9.]/g, '');

            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            this.value = value;
        });

    </script>
@endpush
