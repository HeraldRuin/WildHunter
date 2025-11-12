<form action="<?php echo e(route('boat.search')); ?>" class="form bc_form" method="get">
    <div class="g-field-search">
        <div class="row">
            <?php $boat_search_fields = setting_item_array('boat_search_fields');
                $boat_search_fields = array_values(
                    \Illuminate\Support\Arr::sort($boat_search_fields, function ($value) {
                        return $value['position'] ?? 0;
                    }),
                );
            ?>
            <!--[if BLOCK]><![endif]--><?php if(!empty($boat_search_fields)): ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $boat_search_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $field['title'] = $field['title_'.app()->getLocale()] ?? $field['title'] ?? "" ?>
                    <div class="col-md-<?php echo e($field['size'] ?? '6'); ?> border-right">
                        <!--[if BLOCK]><![endif]--><?php switch($field['field']):
                            case ('service_name'): ?>
                                <?php echo $__env->make('Boat::frontend.layouts.search.fields.service_name', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>

                            <?php case ('location'): ?>
                                <?php echo $__env->make('Boat::frontend.layouts.search.fields.location', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>

                            <?php case ('date'): ?>
                                <?php echo $__env->make('Boat::frontend.layouts.search.fields.date', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>

                            <?php case ('attr'): ?>
                                <?php echo $__env->make('Boat::frontend.layouts.search.fields.attr', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>
                        <?php endswitch; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
    <div class="g-button-submit">
        <button class="btn btn-primary btn-search" type="submit"><?php echo e(__('Search')); ?></button>
    </div>
</form>
<?php /**PATH /var/www/bc-cms/themes/BC/Boat/Views/frontend/layouts/search/form-search.blade.php ENDPATH**/ ?>