<div class="bc-list-news">
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
            <div class="row">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-4 col-md-6">
                        <?php echo $__env->make('News::frontend.blocks.list-news.loop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/bc-cms/themes/BC/News/Views/frontend/blocks/list-news/index.blade.php ENDPATH**/ ?>