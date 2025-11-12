<?php
    $translation = $row->translate();
?>
<div class="item">
    <!--[if BLOCK]><![endif]--><?php if($row->is_featured == "1"): ?>
        <div class="featured">
            <?php echo e(__("Featured")); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <div class="header-thumb">
        <!--[if BLOCK]><![endif]--><?php if($row->discount_percent): ?>
            <div class="sale_info"><?php echo e($row->discount_percent); ?></div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($row->image_url): ?>
            <!--[if BLOCK]><![endif]--><?php if(!empty($disable_lazyload)): ?>
                <img src="<?php echo e($row->image_url); ?>" class="img-responsive" alt="<?php echo e($location->name ?? ''); ?>">
            <?php else: ?>
                <?php echo get_image_tag($row->image_id,'medium',['class'=>'img-responsive','alt'=>$row->title]); ?>

            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <a class="st-btn st-btn-primary tour-book-now" href="<?php echo e($row->getDetailUrl()); ?>"><?php echo e(__("Book now")); ?></a>
        <div class="service-wishlist <?php echo e($row->isWishList()); ?>" data-id="<?php echo e($row->id); ?>" data-type="<?php echo e($row->type); ?>">
            <i class="fa fa-heart"></i>
        </div>
    </div>
    <div class="caption clear">
        <div class="title-address">
            <h3 class="title"><a href="<?php echo e($row->getDetailUrl()); ?>"> <?php echo e($translation->title); ?> </a></h3>
            <p class="duration">
                <span>
                    <?php echo e(duration_format($row->duration)); ?>

                </span>
                <!--[if BLOCK]><![endif]--><?php if(!empty($row->location->name)): ?>
                    -
                    <i>
                        <?php $location =  $row->location->translate() ?>
                        <?php echo e($location->name ?? ''); ?>

                    </i>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </p>
        </div>
        <div class="g-price">
            <div class="price">
                <span class="onsale"><?php echo e($row->display_sale_price); ?></span>
                <span class="text-price"><?php echo e($row->display_price); ?></span>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/bc-cms/themes/BC/Tour/Views/frontend/blocks/list-tour/loop-box-shadow.blade.php ENDPATH**/ ?>