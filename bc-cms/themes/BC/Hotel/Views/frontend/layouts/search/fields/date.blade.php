<div class="form-group">
    <i class="field-icon icofont-wall-clock"></i>
    <div class="form-content">
        <div class="form-date-search-hotel">
            <div class="date-wrapper">
                <div class="check-in-wrapper">
                    <label>{{ $field['title'] }}</label>
                    <div class="render check-in-render">{{Request::query('start',display_date(strtotime("today")))}}</div>
                    <span> - </span>
                    <div class="render check-out-render">{{Request::query('end',display_date(strtotime("+1 day")))}}</div>
                </div>
                <button type="button"
                        id="clear-data"
                        class="clear-animal-btn btn btn-sm btn-light"
                        style="position:absolute; right:2px; top:30%; transform:translateY(-50%)">
                    ✕
                </button>
            </div>
            <input type="hidden" class="check-in-input" value="{{Request::query('start',display_date(strtotime("today")))}}" name="start">
            <input type="hidden" class="check-out-input" value="{{Request::query('end',display_date(strtotime("+1 day")))}}" name="end">
            <input type="text" class="check-in-out"  value="{{Request::query('date',date("d.m.Y")." - ".date("d.m.Y",strtotime("+1 day")))}}">
        </div>
    </div>
</div>
