<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php echo e(_i('Administration')); ?>

    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item" href="/message/sendAll"><?php echo e(_i('Send message to all')); ?></a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/users"><?php echo e(_i('Observers')); ?></a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/object/check"><?php echo e(_i("Check Objects")); ?></a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/instrument/admin"><?php echo e(_i("Instruments")); ?></a>
        <a class="dropdown-item" href="/location/admin"><?php echo e(_i("Locations")); ?></a>
        <a class="dropdown-item" href="/eyepiece/admin"><?php echo e(_i("Eyepieces")); ?></a>
        <a class="dropdown-item" href="/filter/admin"><?php echo e(_i("Filters")); ?></a>
        <a class="dropdown-item" href="/lens/admin"><?php echo e(_i("Lens")); ?></a>
    </div>
</li>
