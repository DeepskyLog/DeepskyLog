<nav class="navbar navbar-expand-lg navbar-light bg-light border-top border-bottom">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader2" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarHeader2">
        <ul class="navbar-nav mr-auto">
            <?php if(auth()->guard()->check()): ?>
                <?php echo $__env->make('layout.subheader.location', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->make('layout.subheader.instrument', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>

            <?php echo $__env->make('layout.subheader.list', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php echo $__env->make('layout.subheader.date', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </ul>
    </div>
</nav>
