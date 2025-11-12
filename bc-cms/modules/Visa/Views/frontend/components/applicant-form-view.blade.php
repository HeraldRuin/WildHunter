<div x-data="{step: $wire.get('stepIndex')}">
    <div class="row">
        @if(!empty($this->steps))
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    @foreach($this->steps as $index=>$step)
                        <button 
                            class="border-0 nav-link mb-2" 
                            x-on:click="step = '{{ $index }}'" 
                            type="button" 
                            role="tab" 
                            x-bind:class="{ 'active': step == '{{ $index }}' }"
                        >
                        {{ $index + 1 }}. {{ $step['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="col-md-9">
                @foreach($this->steps as $index=>$step)
                    <div class="card" x-show="step == '{{ $index }}'">
                        <div class="card-body">
                            @foreach($step['children'] as $child)
                                @include('Visa::frontend.components.applicant-form-view.field', ['field' => $child])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>