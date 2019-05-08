<?php $__env->startSection('title'); ?>
    <?php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All lenses");
    } else {
        echo _i("Lenses of %s", Auth::user()->name);
    }
    ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
	<h4>
        <!-- We have to check if admin is part of request, because we can not
            add a variable to the view, because we are using YarJa dataTables. -->
        <?php if (strpos(Request::url(), 'admin') !== false) {
            echo _i("All lenses");
        } else {
            echo _i("Lenses of %s", Auth::user()->name);
        }
        ?>
    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lens/create">
        <?php echo e(_i("Add lens")); ?>

    </a>
    <br /><br />

    <?php echo $dataTable->table(['class' => 'table table-sm table-striped table-hover']); ?>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<?php echo $dataTable->scripts(); ?>


<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>