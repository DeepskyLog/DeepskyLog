<?php $__env->startSection('title', _i('User Administration')); ?>

<?php $__env->startSection('content'); ?>

<div class="col-lg-10 col-lg-offset-1">
    <h3>
        <i class="fa fa-users"></i> <?php echo e(_i('User Administration')); ?>

    </h3>
    <hr>

    <?php echo $dataTable->table(['class' => 'table table-sm table-striped table-hover']); ?>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $dataTable->scripts(); ?>

<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>