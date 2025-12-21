{{--@extends('layouts.user')--}}
{{--@section('content')--}}
{{--    <h2 class="title-bar">--}}
{{--        {{!empty($recovery) ?__('Recovery Animal') : __("Manage Animals")}}--}}
{{--        @if(Auth::user()->hasPermission('animal_create') && empty($recovery))--}}
{{--            <a href="{{ route("animal.vendor.create") }}" class="btn-change-password">{{__("Add Animal")}}</a>--}}
{{--        @endif--}}
{{--    </h2>--}}
{{--    @include('admin.message')--}}
{{--    @if($rows->total() > 0)--}}
{{--        <div class="bc-list-item">--}}
{{--            <div class="bc-pagination">--}}
{{--                <span class="count-string">{{ __("Showing :from - :to of :total animals",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>--}}
{{--                {{$rows->appends(request()->query())->links()}}--}}
{{--            </div>--}}
{{--            <div class="list-item">--}}
{{--                <div class="row">--}}
{{--                    @foreach($rows as $row)--}}
{{--                        <div class="col-md-12">--}}
{{--                            @include('Animal::frontend.manageAnimal.loop-list')--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="bc-pagination">--}}
{{--                <span class="count-string">{{ __("Showing :from - :to of :total animals",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>--}}
{{--                {{$rows->appends(request()->query())->links()}}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @else--}}
{{--        {{__("No Animal")}}--}}
{{--    @endif--}}
{{--@endsection--}}

@extends('layouts.user')

@section('content')
    <h2 class="title-bar">{{ __('Manage Animals') }}</h2>

    @if($rows->count())
        <div id="animal-app" class="row mt-4">
            <div class="col-md-3">
                <ul class="nav flex-column nav-pills" id="animalList" role="tablist">
                    @foreach($rows as $k => $animal)
                        <li class="nav-item">
                            <a class="nav-link {{ $k === 0 ? 'active' : '' }}"
                               id="animal-tab-{{ $animal->id }}"
                               data-toggle="pill"
                               href="#animal-{{ $animal->id }}"
                               role="tab"
                               aria-controls="animal-{{ $animal->id }}"
                               aria-selected="{{ $k === 0 ? 'true' : 'false' }}">
                                {{ $animal->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-9">
                <div class="tab-content" id="animalTabContent">
                    @foreach($rows as $k => $animal)
                        <div class="tab-pane fade {{ $k === 0 ? 'show active' : '' }}"
                             id="animal-{{ $animal->id }}"
                             role="tabpanel"
                             aria-labelledby="animal-tab-{{ $animal->id }}">
                            @include('Animal::frontend.manageAnimal.loop-list', ['animal' => $animal])

                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @else
        <p>{{ __("No Animal") }}</p>
    @endif
@endsection
