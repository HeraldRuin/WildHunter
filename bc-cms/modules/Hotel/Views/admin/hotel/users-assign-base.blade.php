<div class="panel">
    <div class="panel-title"><strong>{{__('hotelAdmin.name.user_assign_base_tittle')}}</strong></div>
    <div class="panel-body">

        @if($assignedAdmin)
            <div class="alert alert-info mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div>
                            <strong>{{ __('hotelAdmin.name.current_base_admin') }}</strong>
                        </div>
                        <div class="mt-1">
                            {{ $assignedAdmin->first_name }}
                            {{ $assignedAdmin->last_name }}
                        </div>
                        <div class="text-muted">
                            {{ $assignedAdmin->email }}
                        </div>
                    </div>
                    @if(!empty($row->id))
                        <form action="{{ route('hotel.admin.unassignAdmin', ['id' => $row->id]) }}" method="post" class="ml-3">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger p-0">
                                {{ __('hotelAdmin.buttons.unassign_admin') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="form-group">
            <div class="">
                <select id="usersSelect" name="admin_base" class="form-control">
                    <option value="" hidden>
                        {{ __('hotelAdmin.name.user_search') }}
                    </option>
                    @foreach($baseAdmins as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
