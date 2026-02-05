<tr data-id="{{ $additional->id }}">
    <td>
        <input type="text" name="name" class="form-control" value="{{ $additional->name }}"
               @if($additional->name === 'Питание') readonly @endif>
    </td>

    <td>
        <input type="number"
               name="price"
               step="0.01"
               class="form-control"
               value="{{ $additional->price }}">
    </td>

    <td class="text-center">
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
