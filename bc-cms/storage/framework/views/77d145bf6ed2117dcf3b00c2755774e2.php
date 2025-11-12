<div class="container">
    <!--[if BLOCK]><![endif]--><?php if($title): ?>
        <div class="title">
            <?php echo e($title); ?>

            <!--[if BLOCK]><![endif]--><?php if(!empty($desc)): ?>
                <div class="sub-title">
                    <?php echo e($desc); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <div class="list-item">
        <!--[if BLOCK]><![endif]--><?php if($style_list === "normal"): ?>
            <div class="row">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-<?php echo e($col ?? 3); ?> col-md-6">
                        <?php echo $__env->make('Tour::frontend.layouts.search.loop-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($style_list === "carousel"): ?>
            <div class="owl-carousel">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('Tour::frontend.layouts.search.loop-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($style_list === "box_shadow"): ?>
            <div class="row row-eq-height">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-<?php echo e($col ?? 4); ?> col-md-6 col-item">
                        <?php echo $__env->make('Tour::frontend.blocks.list-tour.loop-box-shadow', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /var/www/bc-cms/themes/BC/Tour/Views/frontend/blocks/list-tour/style-normal.blade.php ENDPATH**/ ?>