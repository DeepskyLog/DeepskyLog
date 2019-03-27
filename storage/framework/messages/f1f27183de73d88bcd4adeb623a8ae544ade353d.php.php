<?php $__env->startSection('title', _i('User Administration')); ?>

<?php $__env->startSection('content'); ?>

<div class="col-lg-10 col-lg-offset-1">
    <h3>
        <i class="fa fa-users"></i> <?php echo e(_i('User Administration')); ?>

    </h3>
    <hr>
    <table class="table table-sm table-striped table-hover" id="users_table">
        <thead>
            <tr>
                <th><?php echo e(_i('Name')); ?></th>
                <th><?php echo e(_i('Email')); ?></th>
                <th><?php echo e(_i('Date/Time Added')); ?></th>
                <th><?php echo e(_i('User Roles')); ?></th>
                <th><?php echo e(_i('Delete')); ?></th>
                <th><?php echo e(_i('Edit')); ?></th>
                <th><?php echo e(_i('Observations')); ?></th>
                <th><?php echo e(_i('Instruments')); ?></th>
                <th><?php echo e(_i('Lists')); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>

                <td><?php echo e($user->name); ?></td>
                <td><?php echo e($user->email); ?></td>
                <td><?php echo e($user->created_at->format('F d, Y h:ia')); ?></td>
                <td><?php echo e($user->roles()->pluck('name')->implode(', ')); ?></td>
                <td>
                <?php echo Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id] ]); ?>

                <?php echo Form::submit(_i('Delete'), ['class' => 'btn-small']); ?>

                <?php echo Form::close(); ?>


                </td>
                <td>
                    <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="fas fa-user-edit pull-left" style="margin-right: 3px;"></a>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>

    </table>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$.getScript('<?php echo e(URL::asset('js/datatables.js')); ?>', function()
{
    datatable('#users_table', '<?php echo e(LaravelGettext::getLocale()); ?>');
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>