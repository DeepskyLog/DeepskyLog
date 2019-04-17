<?php $__env->startSection('title'); ?>
    <?php if($update): ?>
        <?php echo e($lens->name); ?>

    <?php else: ?>
        <?php echo e(_i("Add a new lens")); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<h4>
    <?php if($update): ?>
        <?php echo e($lens->name); ?>

    <?php else: ?>
        <?php echo e(_i("Add a new lens")); ?>

    <?php endif; ?>
</h4>

<div id="lens">

<?php if($update): ?>
    <form role="form" action="/lens/<?php echo e($lens->id); ?>" method="POST">
    <?php echo method_field('PATCH'); ?>
<?php else: ?>
    <form role="form" action="/lens" method="POST">
<?php endif; ?>
    <?php echo csrf_field(); ?>
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="<?php if($update): ?><?php echo e(_i("Change lens")); ?><?php else: ?><?php echo e(_i("Add lens")); ?><?php endif; ?>">
        <br >
        <?php if(!$update): ?>
        <div class="form-group">
            <label for="catalog"><?php echo e(_i("Select an existing lens")); ?></label>

            <div class="form">
                <select2 class="form-control" @input="selectLens" name="lens" v-model="selected">
                    <?php $__currentLoopData = \App\Lens::all()->unique('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lensloop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option v-bind:value="<?php echo e($lensloop->id); ?>"
                        <?php if($lens->id == $lensloop->id): ?>
                            selected="selected"
                        <?php endif; ?>
                        ><?php echo e($lensloop->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select2>
            </div>
        </div>

        <?php echo e(_i("or specify your lens details manually")); ?>

        <br /><br />

        <?php endif; ?>

        <div class="form-group">
            <label for="name"><?php echo e(_i("Name")); ?></label>
            <input v-model="name" type="text" required class="form-control <?php echo e($errors->has('name') ? 'is-invalid' : ''); ?>" maxlength="64" name="name" size="30" value="<?php if($lens->name): ?><?php echo e($lens->name); ?><?php else: ?><?php echo e(old('name')); ?><?php endif; ?>" />
            <span class="help-block"><?php echo e(_i("e.g. Televue 2x Barlow")); ?></span>
        </div>

        <div class="form-group">
            <label for="factor"><?php echo e(_i("Factor")); ?></label>
            <div class="form-inline">
                <input v-model="factor" type="number" min="0.01" max="9.99" required step="0.01" class="form-control <?php echo e($errors->has('factor') ? 'is-invalid' : ''); ?>" maxlength="5" name="factor" size="5" value="<?php if($lens->factor > 0): ?><?php echo e($lens->factor); ?><?php else: ?><?php echo e(old('factor')); ?><?php endif; ?>" />
            </div>
            <span class="help-block"><?php echo e(_i("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.")); ?></span>
        </div>

        <input type="submit" class="btn btn-success" name="add" value="<?php if($update): ?><?php echo e(_i("Change lens")); ?><?php else: ?><?php echo e(_i("Add lens")); ?><?php endif; ?>" />
    </div>
</form>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    new Vue({
        el: '#lens',
        data: {
            factor: '',
            selected: '',
            name: '',
        },
        methods:{
            selectLens() {
                // create a closure to access component in the callback below
                var self = this
                $.getJSON('/getLensJson/' + this.selected, function(data) {
                    self.name = data.name;
                    self.factor = Math.round(data.factor * 100) / 100;
                });
            }

        }
    })
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>