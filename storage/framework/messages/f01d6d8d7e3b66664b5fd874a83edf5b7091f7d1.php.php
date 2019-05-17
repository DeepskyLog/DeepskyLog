<?php $__env->startSection('title', _i('Observer details')); ?>

<?php $__env->startSection('content'); ?>

<h3><?php echo e($user->name); ?></h3>
<img width="100" style="border-radius: 20%" src="/user/getImage/<?php echo e($user->id); ?>">

<hr>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li class="active nav-item">
        <a class="nav-link active" href="#info" data-toggle="tab">
            <?php echo e(_i("Info")); ?>

        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerYear" data-toggle="tab">
            <?php echo e(_i("Observations per year")); ?>

        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerMonth" data-toggle="tab">
            <?php echo e(_i("Observations per month")); ?>

        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#objectTypes" data-toggle="tab">
            <?php echo e(_i("Object types observed")); ?>

        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#countries" data-toggle="tab">
            <?php echo e(_i("Observations per country")); ?>

        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#stars" data-toggle="tab">
            <?php echo e(_i("DeepskyLog stars")); ?>

        </a>
    </li>
</ul>

<div id="my-tab-content" class="tab-content">
    <!-- Personal tab -->
    <div class="tab-pane active" id="info">
        <table class="table table-striped table-sm">
            <tr>
                <td> <?php echo e(_i("Name")); ?> </td>
                <td> <?php echo e($user->name); ?> </td>
            </tr>

            <!-- Default location -->
            <tr>
                <td> <?php echo e(_i("Default observing site")); ?> </td>
                <td>
                    <a href="/location/3">Location name</a>
                </td>
            </tr>

            <!-- Default instrument -->
            <tr>
                <td> <?php echo e(_i("Default instrument")); ?> </td>
                <td>
                    <a href="/instrument/4">Instrument name</a>
                </td>
            </tr>

            <!-- Number of locations -->
            <tr>
                <td> <?php echo e(_i("Number of locations")); ?> </td>
                <td>
                    17
                </td>
            </tr>

            <!-- Number of instruments -->
            <tr>
                <td> <?php echo e(_i("Number of instruments")); ?> </td>
                <td>
                    7
                </td>
            </tr>

            <!-- Number of eyepieces -->
            <tr>
                <td> <?php echo e(_i("Number of eyepieces")); ?> </td>
                <td>
                    5
                </td>
            </tr>

            <!-- Number of filters -->
            <tr>
                <td> <?php echo e(_i("Number of filters")); ?> </td>
                <td>
                    2
                </td>
            </tr>

            <!-- Number of lenses -->
            <tr>
                <td> <?php echo e(_i("Number of lenses")); ?> </td>
                <td>
                    <?php echo e(count($user->lenses)); ?>

                </td>
            </tr>

            <!-- Country of residence -->
            <tr>
                <td> <?php echo e(_i("Country of residence")); ?> </td>
                <td>
                    <?php if($user->country != ''): ?>
                        <?php echo e(Countries::getOne($user->country, LaravelGettext::getLocaleLanguage())); ?>

                    <?php endif; ?>
                </td>
            </tr>

            <!-- Copyright notice -->
            <tr>
                <td> <?php echo e(_i("Copyright notice")); ?> </td>
                <td> <?php echo $user->getCopyright(); ?> </td>
            </tr>
        </table>

        <table class="table table-striped table-sm">
            <tr>
                <th></th>
                <th> <?php echo e(_i("Total")); ?> </th>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e(_i($type->name)); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Number of observations")); ?> </td>
                <td>36 / 6000 (0.06%)</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>6 / 1000 (0.06%)</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Observations last year")); ?> </td>
                <td>30 / 300 (10.0%)</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>5 / 50 (10.0%)</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Number of drawings")); ?> </td>
                <td>24 / 1200 (0.5%)</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>4 / 2000 (0.5%)</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Drawings last year")); ?> </td>
                <td>6 / 60 (1.0%)</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>1 / 10 (1.0%)</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Different objects")); ?> </td>
                <td>240 / 12000 (2.0%)</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>40 / 2000 (2.0%)</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Messier objects")); ?> </td>
                <td></td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($type->type == "ds"): ?>
                        <td>110 / 110 (100%)</td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Caldwell objects")); ?> </td>
                <td></td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($type->type == "ds"): ?>
                        <td>11 / 110 (10%)</td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("H400 objects")); ?> </td>
                <td></td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($type->type == "ds"): ?>
                        <td>48 / 400 (1.2%)</td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("H400-II objects")); ?> </td>
                <td></td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($type->type == "ds"): ?>
                        <td>24 / 400 (0.6%)</td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <tr>
                <td> <?php echo e(_i("Rank")); ?> </td>
                <td> 17 / 255</td>
                <?php $__currentLoopData = \App\observationTypes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td>12 / 123</td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

        </table>

        <br />
        <a class="btn btn-success" href="/observations/user/<?php echo e($user->id); ?>">
            <i class="far fa-eye"></i>&nbsp;<?php echo e(_i("All observations of ") . $user->name); ?>

        </a>

        <a class="btn btn-success" href="/observations/drawings/user/<?php echo e($user->id); ?>">
            <i class="fas fa-pencil-alt"></i>&nbsp;<?php echo e(_i("All drawings of ") . $user->name); ?>

        </a>

        <?php if($user->id != Auth::user()->id): ?>
            <a class="btn btn-primary" href="/observations/drawings/user/<?php echo e($user->id); ?>">
                <i class="fas fa-envelope-open"></i>&nbsp;<?php echo e(_i("Send message to ") . $user->name); ?>

            </a>
        <?php endif; ?>
    </div>

    <div class="tab-pane" id="observationsPerYear">

            <div id="observationsPerYear"></div>

            <?php echo $observationsPerYear; ?>

    </div>

    <!-- The observations per month page -->
    <div class="tab-pane" id="observationsPerMonth">
            <div id="observationsPerMonth"></div>

            <?php echo $observationsPerMonth; ?>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>