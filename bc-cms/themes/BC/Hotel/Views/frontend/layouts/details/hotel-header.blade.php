<div class="g-header">
    <div class="left">
        @if($row->star_rate)
            <div class="star-rate">
                @for ($star = 1 ;$star <= $row->star_rate ; $star++)
                    <i class="fa fa-star"></i>
                @endfor
            </div>
        @endif
        <h1>{{$translation->title}}</h1>
        @if($translation->address)
            <h2 class="address"><i class="fa fa-map-marker"></i>
                {{$translation->address}}
            </h2>
        @endif
    </div>
</div>
