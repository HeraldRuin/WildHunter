<div class="panel">
    <div class="panel-title"><strong>{{__('Attach an jaeger to the hotel')}}</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label>{{__("Indicate the number of rangers for this base")}}</label>
            <input type="number" value="{{$row->max_hunts_per_day}}"  name="max_hunts_per_day" class="form-control" min="0"
                   step="1"
                   inputmode="numeric"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
    </div>
</div>
