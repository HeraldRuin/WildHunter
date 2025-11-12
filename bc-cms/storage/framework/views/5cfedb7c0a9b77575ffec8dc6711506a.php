<!--[if BLOCK]><![endif]--><?php if($list_item): ?>
    <div class="bc-testimonial">
        <div class="container">
            <h3><?php echo e($title); ?></h3>
            <div class="row">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $list_item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $avatar_url = get_file_url($item['avatar'], 'full') ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="item has-matchHeight">
                            <div class="author">
                                <img src="<?php echo e($avatar_url); ?>" alt="<?php echo e($item['name']); ?>">
                                <div class="author-meta">
                                    <h4><?php echo e($item['name']); ?></h4>
                                    <!--[if BLOCK]><![endif]--><?php if(!empty($item['number_star'])): ?>
                                        <div class="star">
                                            <!--[if BLOCK]><![endif]--><?php for($i = 0 ; $i < $item['number_star'] ; $i++): ?>
                                                <i class="fa fa-star"></i>
                                            <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <p>
                                <?php echo e($item['desc'] ?? ''); ?>

                            </p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /var/www/bc-cms/themes/Base/Template/Views/frontend/blocks/testimonial/index.blade.php ENDPATH**/ ?>