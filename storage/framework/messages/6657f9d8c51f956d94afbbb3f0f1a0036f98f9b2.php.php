<?php $__env->startSection('content'); ?>
<h1>Test</h1>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>