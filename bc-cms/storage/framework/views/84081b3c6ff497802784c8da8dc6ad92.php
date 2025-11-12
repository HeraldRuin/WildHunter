<?php
    /**
    * @var $row \Modules\Location\Models\Location
    * @var $to_location_detail bool
    * @var $service_type string
    */
    $translation = $row->translate();
    $link_location = false;
    if(is_string($service_type)){
        $link_location = $row->getLinkForPageSearch($service_type);
    }
    if(is_array($service_type) and count($service_type) == 1){
        $link_location = $row->getLinkForPageSearch($service_type[0] ?? "");
    }
    if($to_location_detail){
        $link_location = $row->getDetailUrl();
    }
?>
<div class="destination-item <?php if(!$row->image_id): ?> no-image  <?php endif; ?>">
    <!--[if BLOCK]><![endif]--><?php if(!empty($link_location)): ?> <a href="<?php echo e($link_location); ?>">  <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <div class="image" <?php if($row->image_id): ?> style="background: url(<?php echo e($row->getImageUrl()); ?>)" <?php endif; ?> >
            <div class="effect"></div>
            <div class="content">
                <h4 class="title"><?php echo e($translation->name); ?></h4>
                <!--[if BLOCK]><![endif]--><?php if( !empty($layout) and ($layout == "style_1" or $layout == "style_3" or $layout == "style_4")): ?>
                    <!--[if BLOCK]><![endif]--><?php if(is_array($service_type)): ?>
                        <div class="desc">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $service_type; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $count = $row->getDisplayNumberServiceInLocation($type) ?>
                                <!--[if BLOCK]><![endif]--><?php if(!empty($count)): ?>
                                    <!--[if BLOCK]><![endif]--><?php if(empty($link_location)): ?>
                                        <a class="text-white" href="<?php echo e($row->getLinkForPageSearch( $type )); ?>" target="_blank">
                                            <?php echo e($count); ?>

                                        </a>
                                    <?php else: ?>
                                        <span><?php echo e($count); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php else: ?>
                        <!--[if BLOCK]><![endif]--><?php if(!empty($text_service = $row->getDisplayNumberServiceInLocation($service_type))): ?>
                            <div class="desc"><?php echo e($text_service); ?></div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <!--[if BLOCK]><![endif]--><?php if(!empty($link_location)): ?> </a> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /var/www/bc-cms/themes/BC/Location/Views/frontend/blocks/list-locations/loop.blade.php ENDPATH**/ ?>