@if(empty($hotel_id))
    <div class="alert alert-warning">
        {{ __('animal.errors.hotel_required') }}
        @if(Auth::user()->hasPermission('hotel_create'))
            <a href="{{ route('hotel.vendor.create') }}" class="alert-link">{{ __('Add Hotel') }}</a>
        @endif
    </div>
@endif
