@foreach ($attrsList as $item)
    @if(empty($item['hide_in_filter_search']))
        @php
            if(!empty($usedAttrs) and !in_array($item->id,$usedAttrs)) continue;
            $translate = $item->translate();
        @endphp
        <div class="b-mb-[15px] b-pb-[15px] b-border-b-[#dbdbdb] b-border-0 b-border-b b-border-solid last:b-border-b-0">
            <div class="b-text-[15px] b-mb-2.5"><strong>{{$translate->name}}</strong></div>
            <ul class="b-p-0 row b-list-none">
                @foreach($item->terms as $term)
                    @php $translate = $term->translate(); @endphp
                    <li class="filter-term-item col-xs-6 col-md-4">
                        <label class="b-mb-2">
                            <input
                                type="checkbox"
                                x-on:change="toggleAttr({{$item->id}},'{{$term->slug}}', $event.target.checked)"
                                x-bind:checked="attrs[{{$item->id}}]?.includes('{{$term->slug}}') ?? false"
                            > {{$translate->name}}
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endforeach
