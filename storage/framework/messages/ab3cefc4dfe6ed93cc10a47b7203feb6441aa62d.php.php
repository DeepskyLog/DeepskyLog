<?php $__env->startSection('code', '403'); ?>
<?php $__env->startSection('title', __('Forbidden')); ?>

<?php $__env->startSection('image'); ?>
    <div style="background-image: url(<?php echo e(asset('/svg/403.svg')); ?>);" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('message', __($exception->getMessage() ?: 'Sorry, you are forbidden from accessing this page.')); ?>

<?php echo $__env->make('errors::illustrated-layout', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>