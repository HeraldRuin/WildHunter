<!--[if BLOCK]><![endif]--><?php if(!empty($style) and $style == "carousel" and !empty($list_slider)): ?>
    <div class="effect">
        <div class="owl-carousel">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $list_slider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $img = get_file_url($item['bg_image'],'full') ?>
                <div class="item">
                    <div class="item-bg" style="background-image: linear-gradient(0deg,rgba(0, 0, 0, 0.2),rgba(0, 0, 0, 0.2)),url('<?php echo e($img); ?>') !important"></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="text-heading"><?php echo e($title); ?></h1>
            <div class="sub-heading"><?php echo e($sub_title); ?></div>
            <?php echo $__env->make("Template::frontend.blocks.form-search-all-service.form-search", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/bc-cms/themes/BC/Template/Views/frontend/blocks/form-search-all-service/style-normal.blade.php ENDPATH**/ ?>