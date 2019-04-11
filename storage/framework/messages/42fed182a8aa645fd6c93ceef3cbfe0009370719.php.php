
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php echo e(_i('View')); ?>

    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <?php if(auth()->guard()->check()): ?>
            <a class="dropdown-item" href="/observation"><?php echo e(_i('My observations')); ?></a>
            <a class="dropdown-item" href="/drawings"><?php echo e(_i('My drawings')); ?></a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
            <a class="dropdown-item" href="/observingList"><?php echo e(_i('My observing lists')); ?></a>
            <a class="dropdown-item" href="/session"><?php echo e(_i('My sessions')); ?></a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
            <a class="dropdown-item" href="/instrument"><?php echo e(_i('My instruments')); ?></a>
            <a class="dropdown-item" href="/location"><?php echo e(_i('My locations')); ?></a>
            <a class="dropdown-item" href="/eyepiece"><?php echo e(_i('My eyepieces')); ?></a>
            <a class="dropdown-item" href="/filter"><?php echo e(_i('My filters')); ?></a>
            <a class="dropdown-item" href="/lens"><?php echo e(_i('My lenses')); ?></a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
        <?php endif; ?>
        <a class="dropdown-item" href="/observation/all"><?php echo e(_i('Latest observations')); ?></a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/observer/rank"><?php echo e(_i('Observers')); ?></a>
        <a class="dropdown-item" href="/objects/rank"><?php echo e(_i('Popular objects')); ?></a>
        <a class="dropdown-item" href="/statistics"><?php echo e(_i('Statistics')); ?></a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/catalogs"><?php echo e(_i('Catalogs')); ?></a>
    </div>
</li>
