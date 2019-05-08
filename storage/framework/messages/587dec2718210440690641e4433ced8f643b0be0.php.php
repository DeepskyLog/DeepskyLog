<li class="nav-item">
    <span class="navbar-text">
        <?php echo e(_i('List')); ?>

    </span>&nbsp;&nbsp;&nbsp;&nbsp;

    <select class="form-control selection" id="activateList" onchange="list=this.options[this.selectedIndex].value;">
        <option value="/observingList/activate/none">No list</option>
        <optgroup label="Personal observing lists">
            <option selected value="/observingList/activate/list test">list test</option>
            <option value="/observingList/activate/list orion">list orion</option>
        </optgroup>
        <optgroup label="Public observing lists">
            <option value="/observingList/activate/list pegasus">list pegasus</option>
        </optgroup>
    </select>
    <span class="navbar-text">
        - <a href="/observingList/manage"><?php echo e(_i('Manage list')); ?></a>
    </span>
</li>
