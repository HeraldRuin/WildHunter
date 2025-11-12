@php
    $selected = $this->attrs;
@endphp
@foreach ($attributes as $item)
    @if(empty($item['hide_in_filter_search']))
        @php
            $translate = $item->translate();
        @endphp
        <div class="g-filter-item">
            <div class="item-title">
                <h3> {{$translate->name}} </h3>
                <i class="fa fa-angle-up" aria-hidden="true"></i>
            </div>
            <div class="item-content">
                <ul style="max-height: 180px" class="overflow-auto">
                    @foreach($item->terms as $key => $term)
                        @php $translate = $term->translate(); @endphp
                        <li>
                            <div class="bc-checkbox">
                                <label>
                                    <input x-on:click="{{$scrollIntoViewJsSnippet}}"
                                           @if(in_array($term->slug,$selected[$item->id] ?? [])) checked
                                           @endif type="checkbox" wire:change="toggleTerm({{$item->id}},'{{$term->slug}}')"
                                           value="{{$term->slug}}"> {!! $translate->name !!}
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endforeach
