<?php $__env->startSection('title', "DeepskyLog Privacy Policy"); ?>

<?php $__env->startSection('content'); ?>
<h1>
<?php echo e(_i('DeepskyLog Privacy Policy')); ?>

</h1>
<?php echo e(_i("The majority of information on this site can be accessed without providing any personal information.")); ?>

<?php echo e(_i("In case users want to record observations and get acces to a variety of useful tools, the user is asked to register and provide personal information including name, first name and email address.")); ?>


<?php echo e(_i("This information will be used only for user management and to keep you informed about our activities.")); ?>


<?php echo e(_i("The user has the right at any time, at no cost and upon request, to prohibit the use of his information for the purpose of direct communication.")); ?>


<?php echo e(_i("Your personal information is never passed on to third parties.")); ?>

<br /><br />

<?php echo e(_i("In case the registered user has not recorded any information in DeepskyLog within 24 months after registration, his account will be made obsolete and personal information deleted from the database.")); ?>

<br /><br />

<?php
 echo (sprintf(
        _i(
            "In case of questions or concerns regarding your personal data, do not hesitate to contact us at %sdevelopers@deepskylog.be%s."
        ), "<a href='mailto:developers@deepskylog.be'>", "</a>"
    ));
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>