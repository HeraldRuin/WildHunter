@push('css')
    <link href="{{ asset('themes/bc/dist/frontend/module/tour/css/tour.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/fotorama/fotorama.css') }}" />
@endpush
<div class="bc_detail_tour">
    @include('Layout::parts.bc')
    <div class="bc_content">
        <div class="container">
            <div class="row">
                    <div class="col-md-12 col-lg-9">
                        @php $review_score = $row->review_data @endphp
                        @include('Visa::frontend.layouts.details.visa-detail')
                        @include('Visa::frontend.layouts.details.visa-review')
                    </div>
                    <div class="col-md-12 col-lg-3">
                        @livewire('visa::booking-form', ['row' => $row], key($row->id))
                    </div>
                </div>
            </div>
        </div>
        <div class="bc-more-book-mobile">
            <div class="container">
                <div class="left">
                    <div class="g-price">
                        <div class="prefix">
                            <span class="fr_text">{{ __('from') }}</span>
                        </div>
                        <div class="price">
                            @if($row->original_price)
                            <span class="onsale">{{ format_money($row->original_price) }}</span>
                            @endif
                            <span class="text-price">{{ format_money($row->price) }}</span>
                        </div>
                    </div>
                    @if (setting_item('visa_enable_review'))
                        <?php
                        $reviewData = $row->getScoreReview();
                        $score_total = $reviewData['score_total'];
                        ?>
                        <div class="service-review visa-review-{{ $score_total }}">
                            <div class="list-star">
                                <ul class="booking-item-rating-stars">
                                    <li><i class="fa fa-star-o"></i></li>
                                    <li><i class="fa fa-star-o"></i></li>
                                    <li><i class="fa fa-star-o"></i></li>
                                    <li><i class="fa fa-star-o"></i></li>
                                    <li><i class="fa fa-star-o"></i></li>
                                </ul>
                                <div class="booking-item-rating-stars-active"
                                    style="width: {{ $score_total * 2 * 10 ?? 0 }}%">
                                    <ul class="booking-item-rating-stars">
                                        <li><i class="fa fa-star"></i></li>
                                        <li><i class="fa fa-star"></i></li>
                                        <li><i class="fa fa-star"></i></li>
                                        <li><i class="fa fa-star"></i></li>
                                        <li><i class="fa fa-star"></i></li>
                                    </ul>
                                </div>
                            </div>
                            <span class="review">
                                @if ($reviewData['total_review'] > 1)
                                    {{ __(':number Reviews', ['number' => $reviewData['total_review']]) }}
                                @else
                                    {{ __(':number Review', ['number' => $reviewData['total_review']]) }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
                <div class="right">
                    <a class="btn btn-primary bc-button-book-mobile">{{ __('Book Now') }}</a>
                </div>
            </div>
        </div>
    </div>

@push('js')
    <script type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('libs/fotorama/fotorama.js') }}"></script>
    <script type="text/javascript" src="{{ asset('libs/sticky/jquery.sticky.js') }}"></script>
@endpush
