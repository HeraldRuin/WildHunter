<div class="row">
    <div class="col-lg-3 col-md-12">
        @livewire('visa::filter',['lazy' => true])
    </div>
    <div class="col-lg-9 col-md-12">
        <div class="bc-list-item">
            <div class="topbar-search">
                <h2 class="text result-count">
                    @if($rows->total() > 1)
                        {{ __(":count visas found",['count'=>$rows->total()]) }}
                    @else
                        {{ __(":count visa found",['count'=>$rows->total()]) }}
                    @endif
                </h2>
                <div class="control bc-form-order">
                    @include('Layout::global.search.orderby',['routeName'=>'visa.search','hideMap'=>true])
                </div>
            </div>
            <div class="ajax-search-result">
                <div class="list-item">
                    <div class="row">
                        @if($rows->total() > 0)
                            @foreach($rows as $row)
                                <div class="col-lg-4 col-md-6">
                                    @include('Visa::frontend.layouts.search.loop-grid')
                                </div>
                            @endforeach
                        @else
                            <div class="col-lg-12">
                                {{__("Visa not found")}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bc-pagination">
                    {{$rows->appends(request()->except(['_ajax']))->links()}}
                    @if($rows->total() > 0)
                        <span class="count-string">{{ __("Showing :from - :to of :total visas",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
