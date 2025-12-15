@extends('layouts.user')
@section('content')
    <h2 class="title-bar">
        {{!empty($recovery) ?__('Recovery Animal') : __("Manage Animals")}}
{{--        @if(Auth::user()->hasPermission('animal_create') && empty($recovery))--}}
{{--            <a href="{{ route("animal.vendor.create") }}" class="btn-change-password">{{__("Add Animal")}}</a>--}}
{{--        @endif--}}
    </h2>
    @include('admin.message')
    @if($rows->total() > 0)
        <div class="bc-list-item">
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total animals",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
            <div class="list-item">
                <div class="row">
                    @foreach($rows as $row)
                        <div class="col-md-12">
                            @include('Animal::frontend.manageAnimal.loop-list')
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total animals",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
        </div>
    @else
        {{__("No Animal")}}
    @endif
@endsection
