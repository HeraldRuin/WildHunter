<form class="form bc_form" wire:submit.prevent="submit" method="get" >
    <div class="g-field-search">
        <div class="row">
            <?php $visa_search_fields = setting_item_array('visa_search_fields');
                $visa_search_fields = array_values(
                    \Illuminate\Support\Arr::sort($visa_search_fields, function ($value) {
                        return $value['position'] ?? 0;
                    }),
                );
            ?>
            <!--[if BLOCK]><![endif]--><?php if(!empty($visa_search_fields)): ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $visa_search_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $field['title'] = $field['title_'.app()->getLocale()] ?? $field['title'] ?? "" ?>
                    <div class="col-md-<?php echo e($field['size'] ?? '6'); ?> border-right">
                        <!--[if BLOCK]><![endif]--><?php switch($field['field']):
                            case ('visa_type'): ?>
                                <?php echo $__env->make('Visa::frontend.layouts.search.fields.visa_type', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>

                            <?php case ('to_country'): ?>
                                <?php echo $__env->make('Visa::frontend.layouts.search.fields.to_country', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php break; ?>

                            <?php case ('guests'): ?>
                                <?php echo $__env->make('Visa::frontend.layouts.search.fields.guests', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
    <?php
        $__scriptKey = '350828197-0';
        ob_start();
    ?>
    <script>
        $('.form-date-search',$wire.$el).each(function () {
            var parent = $(this),
                check_in_input = $('.check-in-input', parent),
                check_out_input = $('.check-out-input', parent);

            check_in_input.on('change', function () {
                $wire.set('start', $(this).val(), false);
            });
            check_out_input.on('change', function () {
                $wire.set('end', $(this).val(), false);
            });
        });
        $('[name="to_country"]',$wire.$el).on('change', function () {
            $wire.set('to_country', $(this).val(), false);
        });
        $('[name="visa_type"]',$wire.$el).on('change', function () {
            $wire.set('visa_type', $(this).val(), false);
        });
    </script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH /var/www/bc-cms/themes/BC/Visa/Views/frontend/components/search-form/index.blade.php ENDPATH**/ ?>