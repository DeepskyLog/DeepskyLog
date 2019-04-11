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
                <th><?php echo e(_i('User Role')); ?></th>
                <th><?php echo e(_i('Delete')); ?></th>
                <th><?php echo e(_i('Observations')); ?></th>
                <th><?php echo e(_i('Instruments')); ?></th>
                <th><?php echo e(_i('Lists')); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>

                <td>
                    <a href="<?php echo e(route('users.edit', $user->id)); ?>">
                        <?php echo e($user->name); ?>

                    </a>
                </td>
                <td><?php echo e($user->email); ?></td>
                <td><?php echo e($user->created_at->format('F d, Y h:ia')); ?></td>
                <td><?php echo e($user->type); ?></td>
                <td>
                    <form method="POST" action="<?php echo e(route('users.destroy', $user->id)); ?>">
                        <?php echo method_field('DELETE'); ?>
                        <?php echo csrf_field(); ?>
                        <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </form>
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