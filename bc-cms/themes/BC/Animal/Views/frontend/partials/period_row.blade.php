<tr data-id="{{ $period->id }}">
    <td>
        <input type="date"
               name="start_date"
               class="form-control"
               value="{{ $period->start_date }}">
    </td>
    <td>
        <input type="date"
               name="end_date"
               class="form-control"
               value="{{ $period->end_date }}">
    </td>
    <td>
        <input type="number"
               name="amount"
               min="0"
               class="form-control"
               value="{{ $period->price }}"
               inputmode="numeric"
               pattern="[0-9]*"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </td>
    <td class="text-nowrap text-center align-middle">
        <button class="btn btn-success btn-sm save-period" data-id="{{ $period->id }}">
            {{__("Save")}}
        </button>

        <button class="btn btn-danger btn-sm remove-period" data-id="{{ $period->id }}">
            {{__("Delete")}}
        </button>

    </td>

</tr>

