<?php $__env->startSection('title'); ?>
    <?php echo e(_i("Lenses of %s", "Name")); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
	<h4>
        <!-- TODO: Show real name -->
        <?php echo e(_i("Lenses of %s", "Name")); ?>

    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lens/create">
        <?php echo e(_i("Add lens")); ?>

    </a>
    <br /><br />
    <!-- TODO: Show administration overview page
         TODO: Show one lens (from other observer)
         TODO: Add translation for registrations
    -->

    <table class="table table-sm table-striped table-hover" id="lens_table">
        <thead>
            <tr>
                <th><?php echo e(_i("Name")); ?></th>
                <th><?php echo e(_i("Factor")); ?></th>
                <th><?php echo e(_i("Active")); ?></th>
                <th><?php echo e(_i("Delete")); ?></th>
                <th><?php echo e(_i("Observations")); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- TODO: Only show the lenses for the correct user -->
            <?php $__currentLoopData = \App\Lens::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lens): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <a href="/lens/<?php echo e($lens->id); ?>/edit">
                            <?php echo e($lens->name); ?>

                        </a>
                    </td>
                    <td><?php echo e($lens->factor); ?></td>
                    <td>
                        <form method="POST" action="/lens/<?php echo e($lens->id); ?>">
                            <?php echo method_field('PATCH'); ?>
                            <?php echo csrf_field(); ?>
                            <input type="checkbox" name="active" onChange="this.form.submit()" <?php echo e($lens->active ? 'checked' : ''); ?>>
                        </form>
                    </td>
                    <td>
                        <!-- TODO: Only show if there are no observations with this lens -->
                        <form method="POST" action="/lens/<?php echo e($lens->id); ?>">
                            <?php echo method_field('DELETE'); ?>
                            <?php echo csrf_field(); ?>
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <!-- TODO: Show the correct number of observations with this lens, and make the correct link -->
                        <a href="#">
                        <?php if($lens->id != 6): ?>
                            <?php echo e($lens->id . " " . _i("observations")); ?>

                        <?php else: ?>
                            <?php echo e($lens->id . " " . _i("observation")); ?>

                        <?php endif; ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$.getScript('<?php echo e(URL::asset('js/datatables.js')); ?>', function()
{
    datatable('#lens_table', '<?php echo e(LaravelGettext::getLocale()); ?>');
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>