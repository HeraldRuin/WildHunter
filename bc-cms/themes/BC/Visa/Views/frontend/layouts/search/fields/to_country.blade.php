<div class="form-group">
	<i class="field-icon fa icofont-map"></i>
	<div class="form-content" wire:ignore>
		<label>{{ $field['title'] ?? "" }}</label>
		
            <?php
            $name = "";
            $list_json = [
                [
                    'id'    => '',
                    'title' => __('Any Country'),
                ],
            ];
            $old = !empty($to_country) ? $to_country : '';
            $traverse = function ($countries, $prefix = '') use (&$traverse, &$list_json, &$name, $old) {
                foreach ($countries as $code=>$country) {
                    if ($old == $code) {
                        $name = $country;
                    }
                    $list_json[] = [
                        'id'    => $code,
                        'title' => $country,
                    ];
                }
            };

            $traverse(\Modules\Visa\Models\VisaService::countryList());
            ?>
			<div class="smart-search">
				<input type="text" class="smart-search-location parent_text form-control" readonly placeholder="{{__("Where are you going?")}}" value="{{ $name }}" data-onLoad="{{__("Loading...")}}"
				       data-default="{{ json_encode($list_json) }}">
				<input type="hidden" class="child_id" name="to_country" value="{{$old}}">
			</div>
	</div>
</div>

