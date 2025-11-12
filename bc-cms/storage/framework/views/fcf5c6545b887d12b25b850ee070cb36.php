<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="<?php echo e($html_class ?? ''); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php event(new \Modules\Layout\Events\LayoutBeginHead()); ?>

    <?php echo $__env->make('Layout::parts.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('Layout::parts.seo-meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link href="<?php echo e(asset('libs/bootstrap/css/bootstrap.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('libs/font-awesome/css/font-awesome.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('libs/ionicons/css/ionicons.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('libs/icofont/icofont.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('libs/select2/css/select2.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('themes/bc/dist/frontend/css/notification.css')); ?>" rel="newest stylesheet">
    <link href="<?php echo e(asset('themes/bc/dist/frontend/css/app.css?_ver=' . config('app.asset_version'))); ?>" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('libs/daterange/daterangepicker.css')); ?>">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel='stylesheet' id='google-font-css-css'
        href='https://fonts.googleapis.com/css?family=Poppins%3A300%2C400%2C500%2C600&display=swap' type='text/css'
        media='all' />

    <?php if(setting_item('cookie_agreement_type') == 'cookie_consent'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('libs/cookie-consent/cookieconsent.css')); ?>" media="print"
            onload="this.media='all'">
    <?php endif; ?>

    <?php echo \App\Helpers\Assets::css(); ?>

    <?php echo \App\Helpers\Assets::js(); ?>

    <?php echo $__env->make('Layout::parts.global-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Styles -->
    <?php echo $__env->yieldPushContent('css'); ?>
    
    <link href="<?php echo e(route('core.style.customCss')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('libs/carousel-2/owl.carousel.css')); ?>" rel="stylesheet">
    <?php if(setting_item_with_lang('enable_rtl')): ?>
        <link href="<?php echo e(asset('themes/bc/dist/frontend/css/rtl.css')); ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if(!is_demo_mode()): ?>
        <?php echo setting_item('head_scripts'); ?>

        <?php echo setting_item_with_lang_raw('head_scripts'); ?>

    <?php endif; ?>

</head>

<body
    class="frontend-page <?php echo e(!empty($row->header_style) ? 'header-' . $row->header_style : 'header-normal'); ?> <?php echo e($body_class ?? ''); ?> <?php if(setting_item_with_lang('enable_rtl')): ?> is-rtl <?php endif; ?> <?php if(is_api()): ?> is_api <?php endif; ?>">
    <?php if(!is_demo_mode()): ?>
        <?php echo setting_item('body_scripts'); ?>

        <?php echo setting_item_with_lang_raw('body_scripts'); ?>

    <?php endif; ?>
    <div class="bc_wrap">
        <?php if(auth()->user() and (!empty($adminbar_buttons) || is_admin()) and empty($_GET['preview'])): ?>
            <?php echo $__env->make('Layout::parts.adminbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
        <?php if(!is_api()): ?>
            <?php echo $__env->make('Layout::parts.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('Layout::parts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>

        <?php echo $__env->make('Layout::parts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <?php if(!is_demo_mode()): ?>
        <?php echo setting_item('footer_scripts'); ?>

        <?php echo setting_item_with_lang_raw('footer_scripts'); ?>

    <?php endif; ?>
    <?php echo $__env->make('demo_script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH /var/www/bc-cms/themes/BC/Layout/app.blade.php ENDPATH**/ ?>