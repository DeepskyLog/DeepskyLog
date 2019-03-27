<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <br />
            <!-- Language -->
            <?php echo $__env->make('layout.sidebar.language', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Quickpick -->
            <?php echo $__env->make('layout.sidebar.quickpick', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Moon -->
            <?php echo $__env->make('layout.sidebar.moon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </ul>
    </div>
</nav>
