<?php $__env->startSection('title', _i('Edit User')); ?>

<?php $__env->startSection('content'); ?>

<div class='col-lg-4 col-lg-offset-4'>

    <h3><i class='fa fa-user-plus'></i> <?php echo e(_i('Edit')); ?> <?php echo e($user->name); ?></h3>
    <hr>

    <?php echo e(Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT'))); ?>


    <div class="form-group">
        <?php echo e(Form::label('username', _i('Username'))); ?>

        <?php echo e(Form::text('username', null, ['class' => 'form-control', 'readonly' => 'true'])); ?>

    </div>

    <div class="form-group">
        <?php echo e(Form::label('name', _i('Name'))); ?>

        <?php echo e(Form::text('name', null, array('class' => 'form-control'))); ?>

    </div>

    <div class="form-group">
        <?php echo e(Form::label('email', _i('Email'))); ?>

        <?php echo e(Form::email('email', null, array('class' => 'form-control'))); ?>

    </div>

    <div class="form-group">
        <label for="type"><?php echo e(_i("Role")); ?></label>
        <div class="form">
            <select class="form-control selection" name="type">
                <?php if($user->type == "admin"): ?>
                    <option>default</option>
                    <option selected="selected">admin</option>
                <?php else: ?>
                    <option selected="selected">default</option>
                    <option>admin</option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php echo e(Form::submit(_i('Adapt'), array('class' => 'btn btn-primary'))); ?>


    <?php echo e(Form::close()); ?>


</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>