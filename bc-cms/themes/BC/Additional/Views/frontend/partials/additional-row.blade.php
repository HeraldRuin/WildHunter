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

                <select name="calculation_type" class="form-control">
                    <option value="" hidden
                            @if(empty($additional->calculation_type)) selected @endif>
                        Выберите тип расчета
                    </option>
                    <option value="per_person"
                            @if(($additional->type ?? '') === 'per_person') selected @endif>
                        Кол-во людей
                    </option>

                    <option value="individual"
                            @if(($additional->type ?? '') === 'individual') selected @endif>
                        Индивидуальный
                    </option>
                </select>
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
        $(document).on('keydown', '.price-input', function (e) {
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];

            if (allowedKeys.includes(e.key)) return;

            const value = this.value;

            if (value.length === 0) {
                if (e.key >= '1' && e.key <= '9') return;
                e.preventDefault();
                return;
            }
            if ((e.key >= '0' && e.key <= '9') || (e.key === '.' && !value.includes('.'))) return;

            e.preventDefault();
        });

    </script>
@endpush
