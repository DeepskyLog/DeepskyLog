<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    	<meta name="revisit-after" content="1 day" />
	    <meta name="author" content="DeepskyLog - VVS" />
	    <meta name="keywords" content="VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, Deepsky, waarnemingen, kometen" />
    
        <base href=" . $baseURL . " />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

        <link rel="stylesheet" href="css/app.css">
    	<title><?php echo $__env->yieldContent('title', 'DeepskyLog'); ?></title>
    </head>

    <?php echo $__env->make('layout.header', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <div>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</html>