@extends('layouts.user')

@section('content')
    <h2 class="title-bar">{{ __('Trophy Cost') }}</h2>

    @if($rows->count())
        <div id="trophy-cost-app" class="row mt-4 row-width">

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

                            @if(!empty($animal->trophies) && $animal->trophies->count() > 0)
                                <form method="POST" action="{{ route('animal.vendor.trophy_cost.store') }}" id="trophy-form-{{ $animal->id }}">
                                    @csrf
                                    <input type="hidden" name="animal_id" value="{{ $animal->id }}">

                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>{{__("Trophy Type")}}</th>
                                            <th>{{__("Price")}}</th>
                                            <th></th>
                                        </tr>
                                        </thead>

                                        <tbody id="trophies-{{ $animal->id }}">
                                        @foreach($animal->trophies as $index => $trophy)
                                            <tr data-id="{{ $trophy->id }}">
                                                <td>
                                                    <input type="hidden" name="trophy_costs[{{ $index }}][id]" value="{{ $trophy->id }}">
                                                    <strong>{{ $trophy->type }}</strong>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           name="trophy_costs[{{ $index }}][price]"
                                                           class="form-control trophy-price-input"
                                                           value="{{ $trophy->price }}"
                                                           placeholder="{{__('Enter price')}}"
                                                           min="0"
                                                           step="0.01"
                                                           inputmode="numeric"
                                                           data-trophy-id="{{ $trophy->id }}">
                                                </td>
                                                <td class="text-nowrap text-center align-middle">
                                                    <button type="button" class="btn btn-success btn-sm save-trophy" data-animal-id="{{ $animal->id }}" data-trophy-id="{{ $trophy->id }}">
                                                        {{__("Save")}}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    {{__('No trophy types configured for this animal. Please contact super admin to add trophy classifications.')}}
                                </div>
                            @endif


                                @if(!empty($animal->fines) && $animal->fines->count() > 0)
                                    <form method="POST" action="{{ route('animal.vendor.fines.store') }}" id="fines-form-{{ $animal->id }}">
                                        @csrf
                                        <input type="hidden" name="animal_id" value="{{ $animal->id }}">

                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{__("Type Fines")}}</th>
                                                <th>{{__("Price")}}</th>
                                                <th></th>
                                            </tr>
                                            </thead>

                                            <tbody id="fines-{{ $animal->id }}">
                                            @foreach($animal->fines as $index => $fine)
                                                <tr data-id="{{ $fine->id }}">
                                                    <td>
                                                        <input type="hidden" name="fines_costs[{{ $index }}][id]" value="{{ $fine->id }}">
                                                        <strong>{{ $fine->type }}</strong>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="fines_costs[{{ $index }}][price]"
                                                               class="form-control fine-price-input"
                                                               value="{{ $fine->price }}"
                                                               placeholder="{{__('Enter price')}}"
                                                               min="0"
                                                               step="0.01"
                                                               inputmode="numeric"
                                                               data-trophy-id="{{ $fine->id }}">
                                                    </td>
                                                    <td class="text-nowrap text-center align-middle">
                                                        <button type="button" class="btn btn-success btn-sm save-fine" data-animal-id="{{ $animal->id }}" data-fine-id="{{ $fine->id }}">
                                                            {{__("Save")}}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        {{__('No trophy fines configured for this animal. Please contact super admin to add fines classifications.')}}
                                    </div>
                                @endif

                                @if(!empty($animal->preparations) && $animal->preparations->count() > 0)
                                    <form method="POST" action="{{ route('animal.vendor.preparations.store') }}" id="preparation-form-{{ $animal->id }}">
                                        @csrf
                                        <input type="hidden" name="animal_id" value="{{ $animal->id }}">

                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{__("Type Preparations")}}</th>
                                                <th>{{__("Price")}}</th>
                                                <th></th>
                                            </tr>
                                            </thead>

                                            <tbody id="preparations-{{ $animal->id }}">
                                            @foreach($animal->preparations as $index => $preparation)
                                                <tr data-id="{{ $preparation->id }}">
                                                    <td>
                                                        <input type="hidden" name="preparation_costs[{{ $index }}][id]" value="{{ $preparation->id }}">
                                                        <strong>{{ $preparation->type }}</strong>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="preparation_costs[{{ $index }}][price]"
                                                               class="form-control preparation-price-input"
                                                               value="{{ $preparation->price }}"
                                                               placeholder="{{__('Enter price')}}"
                                                               min="0"
                                                               step="0.01"
                                                               inputmode="numeric"
                                                               data-trophy-id="{{ $preparation->id }}">
                                                    </td>
                                                    <td class="text-nowrap text-center align-middle">
                                                        <button type="button" class="btn btn-success btn-sm save-preparation" data-animal-id="{{ $animal->id }}" data-preparation-id="{{ $preparation->id }}">
                                                            {{__("Save")}}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        {{__('No trophy preparations configured for this animal. Please contact super admin to add preparations classifications.')}}
                                    </div>
                                @endif
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    @endif
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.save-trophy').on('click', function() {
            const $btn = $(this);
            const trophyId = $btn.data('trophy-id');
            const $row = $btn.closest('tr');
            const price = $row.find('.trophy-price-input').val();
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route('animal.vendor.trophy_cost.update_trophy') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    trophy_id: trophyId,
                    price: price
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.status) {
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    let message = '{{__("Error saving trophy cost")}}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
            });
        });
    });

    $(document).ready(function() {
        $('.save-fine').on('click', function() {
            const $btn = $(this);
            const fineId = $btn.data('fine-id');
            const $row = $btn.closest('tr');
            const price = $row.find('.fine-price-input').val();
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route('animal.vendor.trophy_cost.update_fine') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fine_id: fineId,
                    price: price
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.status) {
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    let message = '{{__("Error saving fine cost")}}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
            });
        });
    });

    $(document).ready(function() {
        $('.save-preparation').on('click', function() {
            const $btn = $(this);
            const preparationId = $btn.data('preparation-id');
            const $row = $btn.closest('tr');
            const price = $row.find('.preparation-price-input').val();
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route('animal.vendor.trophy_cost.update_preparation') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    preparation_id: preparationId,
                    price: price
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.status) {
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    let message = '{{__("Error saving preparation cost")}}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
            });
        });
    });
</script>
@endpush

