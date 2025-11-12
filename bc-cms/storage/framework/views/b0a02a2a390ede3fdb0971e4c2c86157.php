<?php ($location_search_style = setting_item('boat_location_search_style')); ?>
<div class="form-group">
    <i class="field-icon fa icofont-map"></i>
    <div class="form-content" wire:ignore>
        <label><?php echo e($field['title']); ?></label>
        <!--[if BLOCK]><![endif]--><?php if($location_search_style=='autocompletePlace'): ?>
            <div class="g-map-place" >
                <input type="text" name="map_place" placeholder="<?php echo e(__("Where are you going?")); ?>"  value="<?php echo e(request()->input('map_place')); ?>" class="form-control border-0">
                <div class="map d-none" id="map-<?php echo e(\Illuminate\Support\Str::random(10)); ?>"></div>
                <input type="hidden" wire:model="map_lat" value="<?php echo e(request()->input('map_lat')); ?>">
                <input type="hidden" wire:model="map_lng" value="<?php echo e(request()->input('map_lng')); ?>">
            </div>

        <?php else: ?>
        <?php
        $location_name = "";
        $list_json = [];
        $traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json , &$location_name) {
            foreach ($locations as $location) {
                $translate = $location->translate();
                if (Request::query('location_id') == $location->id){
                    $location_name = $translate->name;
                }
                $list_json[] = [
                    'id' => $location->id,
                    'title' => $prefix . ' ' . $translate->name,
                ];
                $traverse($location->children, $prefix . '-');
            }
        };
        $traverse($list_location);
        ?>
        <div class="smart-search">
            <input type="text" class="smart-search-location parent_text form-control" <?php echo e(( empty(setting_item("boat_location_search_style")) or setting_item("boat_location_search_style") == "normal" ) ? "readonly" : ""); ?> placeholder="<?php echo e(__("Where are you going?")); ?>" value="<?php echo e($location_name); ?>" data-onLoad="<?php echo e(__("Loading...")); ?>"
                   data-default="<?php echo e(json_encode($list_json)); ?>">
            <input type="hidden" class="child_id" name="location_id" value="<?php echo e(Request::query('location_id')); ?>">
        </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>
<?php /**PATH /var/www/bc-cms/themes/BC/Boat/Views/frontend/layouts/search/fields/location.blade.php ENDPATH**/ ?>