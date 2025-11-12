<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" wire:model="name" required placeholder="{{__("Visa type name")}}" name="name" class="form-control">
</div>
@if(is_default_lang($lang))
    
    <div class="form-group">
        <label>{{__("Status")}}</label>
        <select wire:model="status" name="status" class="form-control">
            <option value="publish">{{__("Publish")}}</option>
            <option value="draft">{{__("Draft")}}</option>
        </select>
    </div>

@endif