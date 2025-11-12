<div class="filter-item">
    <div class="form-group">
        <i class="field-icon icofont-paperclip"></i>
        <div class="form-content" wire:ignore>
            <label><?php echo e($field['title'] ?? ""); ?></label>
            <?php 
            $old = !empty($visa_type) ?? '';
            $list_cat_json = [
                [
                    'id' => '',
                    'title' => __('Any Visa Type'),
                ],
            ];
            $visa_types = \Modules\Visa\Models\VisaType::search()->get();
            $selected = $old ? \Modules\Visa\Models\VisaType::find($old) : null;
            ?>
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $visa_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visa_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $translate = $visa_type->translate();
                $list_cat_json[] = [
                    'id' => $visa_type->id,
                    'title' => $translate->name,
                ];
                ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            <div class="smart-search">
                <input type="text" class="smart-select parent_text form-control" readonly placeholder="<?php echo e(__("All Visa Type")); ?>" value="<?php echo e($selected ? $selected->name ?? '' :''); ?>" data-default="<?php echo e(json_encode($list_cat_json)); ?>">
                <input type="hidden" class="child_id" name="visa_type" value="<?php echo e($old); ?>">
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/bc-cms/themes/BC/Visa/Views/frontend/layouts/search/fields/visa_type.blade.php ENDPATH**/ ?>