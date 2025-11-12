<div class="form-group">
    <i class="field-icon icofont-wall-clock"></i>
    <div class="form-content">
        <div class="form-date-search">
            <div class="date-wrapper">
                <div class="check-in-wrapper">
                    <label>{{ $field['title'] ?? "" }}</label>
                    <div class="render check-in-render">{{$start ?? display_date(strtotime("today"))}}</div>
                    <span> - </span>
                    <div class="render check-out-render">{{$end ?? display_date(strtotime("+1 day"))}}</div>
                </div>
            </div>
            <input type="hidden" class="check-in-input" value="{{$start ?? display_date(strtotime("today"))}}" name="start">
            <input type="hidden" class="check-out-input" value="{{$end ?? display_date(strtotime("+1 day"))}}" name="end">
            <input type="text" class="check-in-out" name="date" value="{{($start ?? date("Y-m-d"))." - ".($end ?? date("Y-m-d",strtotime("+1 day")))}}">
        </div>
    </div>
</div>