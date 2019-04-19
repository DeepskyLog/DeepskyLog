<?php $__env->startSection('title'); ?>
    <?php echo e($lens->name); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h4>
    <?php echo e($lens->name); ?>

</h4>

<table class="table table-sm">
    <tr>
        <td><?php echo e(_i("Type")); ?></td>
        <td><?php echo e(_i("Lens")); ?></td>
    </tr>

    <tr>
        <td><?php echo e(_i("Factor")); ?></td>
        <td><?php echo e($lens->factor); ?></td>
    </tr>
    <tr>
        <td><?php echo e(_i("Owner")); ?></td>
        <td><?php echo e($lens->observer->name); ?></td>
    </tr>
    <tr>
        <td><?php echo e(_i("Number of observations")); ?></td>
        <td><?php echo e($lens->id); ?></td>
    </tr>

</table>

<?php if(auth()->guard()->check()): ?>
    <?php if(Auth::user()->id === $lens->observer_id || Auth::user()->isAdmin()): ?>
    <a href="/lens/<?php echo e($lens->id); ?>/edit">
        <button type="button" class="btn btn-sm btn-primary">
            Edit <?php echo e($lens->name); ?>

        </button>
    </a>
    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>