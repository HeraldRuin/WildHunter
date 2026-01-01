@extends('layouts.user')

@section('content')
    <h2 class="title-bar">{{ __('Hunting organization') }}</h2>

    @if($rows->count())
        <div id="animal-app" class="row mt-4 row-width">

            <div class="col-md-3">
                <ul class="nav nav-tabs flex-column custom-nav-style">
                    @foreach($rows as $k => $animal)
                        <li class="nav-item">
                            <a class="nav-link {{ $k === 0 ? 'active' : '' }}"
                               data-toggle="tab"
                               href="#animal-{{ $animal->id }}">
                                {{ $animal->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-9">
                <div class="tab-content">

                    @foreach($rows as $k => $animal)
                        <div class="tab-pane fade {{ $k === 0 ? 'show active' : '' }}"
                             id="animal-{{ $animal->id }}">

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{__("From")}}</th>
                                    <th>{{__("To")}}</th>
                                    <th>{{__("Amount")}}</th>
                                    <th></th>
                                </tr>
                                </thead>

                                <tbody id="periods-{{ $animal->id }}">
                                @foreach($animal->periods ?? [] as $period)
                                    @include('Animal::frontend.partials.period_row', [
                                        'period' => $period
                                    ])
                                @endforeach
                                </tbody>
                            </table>

                            <button class="btn btn-primary"
                                    @click="addPeriod({{ $animal->id }}, '{{ route('animal.vendor.period.create', ['animal' => $animal->id]) }}')">
                                {{__("Add Period")}}
                            </button>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    @endif
@endsection

