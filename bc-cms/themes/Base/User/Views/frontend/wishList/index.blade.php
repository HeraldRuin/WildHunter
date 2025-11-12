@extends('layouts.user')
@section('content')
    <h2 class="title-bar">
        {{__("WishList")}}
    </h2>
    @include('admin.message')
    @if($rows->total() > 0)
        <div class="bc-list-item">
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
            <div class="list-item">
                <div class="row">
                    @foreach($rows as $row)
                        <div class="col-md-12">
                            @include('User::frontend.wishList.loop-list')
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bc-pagination">
                <span class="count-string">{{ __("Showing :from - :to of :total",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                {{$rows->appends(request()->query())->links()}}
            </div>
        </div>
    @else
        {{__("No Items")}}
    @endif
@endsection
