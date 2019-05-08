<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    	<meta name="revisit-after" content="1 day" />
	    <meta name="author" content="DeepskyLog - VVS" />
	    <meta name="keywords" content="VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, astronomy, Deepsky, deep-sky, waarnemingen, observations, kometen, comets, planeten, planets, moon, maan" />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

        <link type="text/css" rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>">

        <script type="text/javascript" src="<?php echo e(asset('/js/app.js')); ?>"></script>

    	<title><?php echo $__env->yieldContent('title', 'DeepskyLog'); ?></title>
    </head>

    <body>
        <?php echo $__env->make('layout.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('layout.subheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="container-fluid">
            <div class="row">
                <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                    <?php echo $__env->make('layout.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php echo $__env->make('layout.flash', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <br />
                    <?php echo $__env->yieldContent('content'); ?>
                </main>
            </div>
        </div>

        <br />
        <?php echo $__env->make('layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <script>
            $(document).ready(function() {
                $(".selection").select2();
            });
            $('.selection').select2({
                theme: 'bootstrap4',
            });
        </script>
        <!-- App scripts -->
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
