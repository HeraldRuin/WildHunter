<div class="form-group">
	<i class="field-icon fa icofont-map"></i>
	<div class="form-content" wire:ignore>
		<label><?php echo e($field['title'] ?? ""); ?></label>
		
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
				<input type="text" class="smart-search-location parent_text form-control" readonly placeholder="<?php echo e(__("Where are you going?")); ?>" value="<?php echo e($name); ?>" data-onLoad="<?php echo e(__("Loading...")); ?>"
				       data-default="<?php echo e(json_encode($list_json)); ?>">
				<input type="hidden" class="child_id" name="to_country" value="<?php echo e($old); ?>">
			</div>
	</div>
</div>

<?php /**PATH /var/www/bc-cms/themes/BC/Visa/Views/frontend/layouts/search/fields/to_country.blade.php ENDPATH**/ ?>