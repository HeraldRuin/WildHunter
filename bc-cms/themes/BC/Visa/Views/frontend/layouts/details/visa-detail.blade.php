<div class="g-header">
    <div class="left">
        <h1>{{$translation->title}}</h1>
    </div>
    <div class="right">
        @if(setting_item('visa_enable_review') and $review_score)
            <div class="review-score">
                <div class="head">
                    <div class="left">
                        <span class="head-rating">{{$review_score['score_text']}}</span>
                        <span class="text-rating">{{__("from :number reviews",['number'=>$review_score['total_review']])}}</span>
                    </div>
                    <div class="score">
                        {{$review_score['score_total']}}<span>/5</span>
                    </div>
                </div>
                <div class="foot">
                    {{__(":number% of guests recommend",['number'=>$row->recommend_percent])}}
                </div>
            </div>
        @endif
    </div>
</div>
@if(!empty($row->visaType) or !empty($row->to_country) or !empty($row->processing_days))
    <div class="g-tour-feature">
    <div class="row">
        @if($row->to_country)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-globe"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Country")}}</h4>
                        <p class="value">
                            {{$row->country ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if(!empty($row->visaType))
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-beach"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Visa Type")}}</h4>
                        <p class="value">
                            {{$row->visaType->name ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if(!empty($row->code))
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-code"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Code")}}</h4>
                        <p class="value">
                            {{$row->code ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($row->processing_days)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-wall-clock"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Processing Time")}}</h4>
                        <p class="value">
                            {{__(":amount day(s)",["amount"=>$row->processing_days])}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($row->max_stay_days)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-wall-clock"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Max Stay Days")}}</h4>
                        <p class="value">
                            {{__(':amount day(s)', ['amount' => $row->max_stay_days])}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($row->multiple_entry)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-wall-clock"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Multiple Entry")}}</h4>
                        <p class="value">
                            {{$row->multiple_entry}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@if($translation->content)
    <div class="g-overview">
        <h3>{{__("Overview")}}</h3>
        <div class="description">
            <?php echo $translation->content ?>
        </div>
    </div>
@endif
