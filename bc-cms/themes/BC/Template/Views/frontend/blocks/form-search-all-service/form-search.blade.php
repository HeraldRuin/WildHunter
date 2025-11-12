@if (empty($hide_form_search))
    <div class="g-form-control">
        <ul class="nav nav-tabs" role="tablist">
            @if (!empty($service_types))
                @php $number = 0; @endphp
                @foreach ($service_types as $service_type)
                    @php
                        $allServices = get_bookable_services();
                        if (empty($allServices[$service_type])) {
                            continue;
                        }
                        $module = new ($allServices[$service_type])();
                    @endphp
                    <li role="bc_{{ $service_type }}">
                        <a href="#bc_{{ $service_type }}" class="@if ($number == 0) active @endif"
                            aria-controls="bc_{{ $service_type }}" role="tab" data-toggle="tab">
                            <i class="{{ $module->getServiceIconFeatured() }}"></i>
                            {{ !empty($modelBlock['title_for_' . $service_type]) ? $modelBlock['title_for_' . $service_type] : $module->getModelName() }}
                        </a>
                    </li>
                    @php $number++; @endphp
                @endforeach
            @endif
        </ul>
        <div class="tab-content">
            @if (!empty($service_types))
                @php $number = 0; @endphp
                @foreach ($service_types as $service_type)
                    @php
                        $allServices = get_bookable_services();
                        if (empty($allServices[$service_type])) {
                            continue;
                        }
                        $module = new ($allServices[$service_type])();
                    @endphp
                    <div role="tabpanel" class="tab-pane @if ($number == 0) active @endif"
                        id="bc_{{ $service_type }}">
                        @switch($service_type)
                            @case('visa')
                            @case('tour')
                                @livewire($service_type.'::search-form', ['shouldRedirect' => true])
                                @break
                            @default
                                @includeIf(ucfirst($service_type) . '::frontend.layouts.search.form-search')
                            @break
                        @endswitch
                    </div>
                    @php $number++; @endphp
                @endforeach
            @endif
        </div>
    </div>
@endif
