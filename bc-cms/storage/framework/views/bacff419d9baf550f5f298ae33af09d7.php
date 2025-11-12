<div class="container">
    <div class="bc-list-locations <?php if(!empty($layout)): ?> <?php echo e($layout); ?> <?php endif; ?>">
        <div class="title">
            <?php echo e($title); ?>

        </div>
        <!--[if BLOCK]><![endif]--><?php if(!empty($desc)): ?>
            <div class="sub-title">
                <?php echo e($desc); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if(!empty($rows)): ?>
            <div class="list-item">
                <div class="row">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                        $size_col = 4;
                        if( !empty($layout) and (  $layout == "style_2" or $layout == "style_3" or $layout == "style_4" )){
                            $size_col = 4;
                        }else{
                            if($key == 0){
                                $size_col = 8;
                            }
                        }
                        ?>
                        <div class="col-lg-<?php echo e($size_col); ?> col-md-6">
                            <?php echo $__env->make('Location::frontend.blocks.list-locations.loop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /var/www/bc-cms/themes/BC/Location/Views/frontend/blocks/list-locations/index.blade.php ENDPATH**/ ?>