@extends('layouts.user')
@section('content')
    <h2 class="title-bar">
        {{!empty($recovery) ?__('Recovery Hotels') : __("Manage Hotels")}}
        @if(Auth::user()->hasPermission('hotel_create') && empty($recovery))
            @if($viewAdminCabinet && $isAdmin)
                <a href="{{ route("hotel.vendor.create", ['user' => $user->id, 'viewAdminCabinet' => 1]) }}"
                   class="btn-change-password">
                    {{ __("Add Hotel") }}
                </a>
            @else
                <a href="{{ route("hotel.vendor.create") }}" class="btn-change-password">
                    {{ __("Add Hotel") }}
                </a>
            @endif
        @endif
    </h2>
    @include('admin.message')
    @if($rows->total() > 0)
        <div class="bc-list-item">
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total Hotels",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
            <div class="list-item">
                <div class="row">
                    @foreach($rows as $row)
                        <div class="col-md-12">
                            @include('Hotel::frontend.vendorHotel.loop-list')
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total Hotels",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
        </div>
    @else
        {{__("No Hotel")}}
    @endif
@endsection
