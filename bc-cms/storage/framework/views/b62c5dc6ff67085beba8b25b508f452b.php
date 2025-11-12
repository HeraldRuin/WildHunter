<div class="bc-form-search-all <?php echo e($style); ?> <?php if(!empty($style) and $style == "carousel"): ?> bc-form-search-slider <?php endif; ?>" <?php if(empty($style)): ?> style="background-image: linear-gradient(0deg,rgba(0, 0, 0, 0.2),rgba(0, 0, 0, 0.2)),url('<?php echo e($bg_image_url); ?>') !important" <?php endif; ?>>
    <!--[if BLOCK]><![endif]--><?php if(in_array($style,['carousel',''])): ?>
        <?php echo $__env->make("Template::frontend.blocks.form-search-all-service.style-normal", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <!--[if BLOCK]><![endif]--><?php if($style == "carousel_v2"): ?>
        <?php echo $__env->make("Template::frontend.blocks.form-search-all-service.style-slider-ver2", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <!--[if BLOCK]><![endif]--><?php if($style == "bg_video"): ?>
        <?php echo $__env->make("Template::frontend.blocks.form-search-all-service.style-video", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /var/www/bc-cms/themes/BC/Template/Views/frontend/blocks/form-search-all-service/index.blade.php ENDPATH**/ ?>