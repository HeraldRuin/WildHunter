<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __('General Settings') }}</h3>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                @if (is_default_lang())
                    <div class="form-group">
                        <label>{{ __('Teacher Role') }}</label>
                        <div class="form-controls">
                            <select name="teacher_role_id" class="form-control">
                                @foreach (\Modules\User\Models\Role::all() as $role)
                                    <option value="{{ $role->id }}"
                                        {{ setting_item('teacher_role_id', 2) == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <p>{{ __('You can edit on main lang.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
