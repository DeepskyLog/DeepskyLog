
<?php if(sizeof(laraflash()->toArray()) > 0): ?>
    <br />
    <div class="container">
        <?php echo laraflash()->render(); ?>

    </div>
<?php endif; ?>
