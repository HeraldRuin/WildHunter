@foreach($children as $index => $child)
    @if(componentExists($child['type']))
        @livewire($child['type'], $child['model'] ?? [], key($child['type'].'-'.$index))
    @endif
@endforeach

