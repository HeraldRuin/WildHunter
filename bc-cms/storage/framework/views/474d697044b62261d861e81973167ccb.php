<div class="container">
    <div class="bc-list-space layout_<?php echo e($style_list); ?>">
        <!--[if BLOCK]><![endif]--><?php if($title): ?>
        <div class="title">
            <?php echo e($title); ?>

        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($desc): ?>
            <div class="sub-title">
                <?php echo e($desc); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <div class="list-item">
            <!--[if BLOCK]><![endif]--><?php if($style_list === "normal"): ?>
                <div class="row">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-<?php echo e($col ?? 3); ?> col-md-6">
                            <?php echo $__env->make('Space::frontend.layouts.search.loop-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <!--[if BLOCK]><![endif]--><?php if($style_list === "carousel"): ?>
                <div class="owl-carousel">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('Space::frontend.layouts.search.loop-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div><?php /**PATH /var/www/bc-cms/themes/BC/Space/Views/frontend/blocks/list-space/index.blade.php ENDPATH**/ ?>