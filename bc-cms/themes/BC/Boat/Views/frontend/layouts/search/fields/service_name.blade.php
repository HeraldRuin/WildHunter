<div class="form-group">
    <i class="field-icon fa icofont-search"></i>
    <div class="form-content">
        <label>{{ $field['title'] }}</label>
        <div class="input-search">
            <input type="text" wire:model="service_name" class="form-control" placeholder="{{__("Search for...")}}" value="{{ request()->input("service_name") }}">
        </div>
    </div>
</div>