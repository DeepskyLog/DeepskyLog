<?php if(flash()->message): ?>
    <div class="container">
        <br />
        <div class="<?php echo e(flash()->class); ?>">
            <?php echo e(flash()->message); ?>

        </div>
    </div>
<?php endif; ?>
