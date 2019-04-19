<?php $__env->startSection('title'); ?>
    <?php echo e(_i('Verify Your Email Address')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?php echo e(_i('Verify Your Email Address')); ?></div>

                <div class="card-body">
                    <?php if(session('resent')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(_i('A fresh verification link has been sent to your email address.')); ?>

                        </div>
                    <?php endif; ?>

                    <?php echo e(_i('Before proceeding, please check your email for a verification link.')); ?>

                    <?php
                        $route = route('verification.resend');
                        $string = '<a href="' . $route . '">';
                        echo sprintf(_i('If you did not receive the email, %sclick here to request another%s.'), $string, '</a>');
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>