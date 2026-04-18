<tr data-id="{{ $additional->id }}" style="vertical-align: bottom;">
    <td style="width: 70%;">
        <div class="d-flex gap-3 align-items-end w-100">
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ $additional->name }}"
                   @if($additional->name === 'Питание') readonly @endif>

            @if($additional->name !== 'Питание')
                <div class="d-flex flex-column gap-1">
                    <label class="text-muted small mb-0">Количество</label>
                <input type="text"
                       name="count"
                       class="form-control"
                       style="width: 90px;"
                       value="{{ $additional->count ?? '' }}"
                       placeholder="кол-во">
                </div>

                <div class="d-flex flex-column gap-1 flex-grow-1">
                    <label class="text-muted small mb-0">Тип расчета</label>
                <select name="calculation_type" class="form-control w-100">
                    <option value="" hidden
                            @if(empty($additional->calculation_type)) selected @endif>
                        Выберите тип
                    </option>
                    <option value="per_person"
                            @if(($additional->calculation_type ?? '') === 'per_person') selected @endif>
                        Кол-во людей
                    </option>

                    <option value="individual"
                            @if(($additional->calculation_type ?? '') === 'individual') selected @endif>
                        Индивидуальный
                    </option>
                </select>
                </div>
            @endif
        </div>
    </td>

    <td class="align-bottom">
        <div class="d-flex align-items-end h-100">
            <input type="text"
                   name="price"
                   class="form-control price-input"
                   value="{{ $additional->price }}"
                   inputmode="decimal">
        </div>
    </td>

    <td class="text-center align-bottom" style="width: 260px">
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
