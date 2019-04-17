<?php $__env->startSection('title'); ?>
    <?php if($user == 'user'): ?>
        <?php echo e(_i("Lenses of %s", Auth::user()->name)); ?>

    <?php else: ?>
        <?php echo e(_i("All lenses")); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div id="lens">

        <div v-if="flasherror" class="alert-danger">
            <?php echo e(_i('Lens deleted: ')); ?> {{ lensname }}
        </div>
        <div v-if="flash" class="alert-warning">
            <div v-if="active">
                <?php echo e(_i('Lens activated: ')); ?> {{ lensname }}
            </div>
            <div v-else>
                <?php echo e(_i('Lens deactivated: ')); ?> {{ lensname }}
            </div>
        </div>
    <br />

	<h4>
        <?php if($user == 'user'): ?>
            <?php echo e(_i("Lenses of %s", Auth::user()->name)); ?>

        <?php else: ?>
            <?php echo e(_i("All lenses")); ?>

        <?php endif; ?>

    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lens/create">
        <?php echo e(_i("Add lens")); ?>

    </a>
    <br /><br />
    <table class="table table-sm table-striped table-hover" id="lens_table">
        <thead>
            <tr>
                <th><?php echo e(_i("Name")); ?></th>
                <th><?php echo e(_i("Factor")); ?></th>
                <?php if($user == 'user'): ?>
                    <th><?php echo e(_i("Active")); ?></th>
                <?php endif; ?>
                <th><?php echo e(_i("Delete")); ?></th>
                <th><?php echo e(_i("Observations")); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Only show the lenses for the correct user -->
            <?php $__currentLoopData = $lenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lens): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <a href="/lens/<?php echo e($lens->id); ?>/edit">
                            <?php echo e($lens->name); ?>

                        </a>
                    </td>
                    <td><?php echo e($lens->factor); ?></td>
                    <?php if($user == 'user'): ?>
                    <td>
                        <lensactivation :selected="<?php echo e($lens->active); ?>" :id="<?php echo e($lens->id); ?>">
                        </lensactivation>
                    </td>
                    <?php endif; ?>
                    <td>
                        <!-- TODO: Only show if there are no observations with this lens -->
                        <lensdeletion name="<?php echo e($lens->name); ?>" deleteid="<?php echo e($lens->id); ?>">
                        </lensdeletion>
                    </td>
                    <td>
                        <!-- TODO: Show the correct number of observations with this lens, and make the correct link -->
                        <a href="#">
                            <?php echo e($lens->id . ' ' . _n('observation', 'observations', $lens->id)); ?>

                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Set the correct language for the datatable
$.getScript('<?php echo e(URL::asset('js/datatables.js')); ?>', function()
{
    datatable('#lens_table', '<?php echo e(LaravelGettext::getLocale()); ?>', [
        // Sort columns naturally
       { type: 'natural' }
     ]);
});

// Remove the row from the datatable if the 'delete' icon is pressed.
$(document).ready(function() {
    $('#lens_table tbody').on( 'click', '#delete', function () {
        var table = $('#lens_table').DataTable();

        table.row( $(this).parents('tr') ).remove().draw();
    } );
});

// Activate select box and methods
Vue.component('lensactivation', {
    template: `
        <input type="checkbox" @change="activateLens" :checked="selected">
    `,
    props: {
        id: { },
        selected: { default: false },
    },
    methods:{
        activateLens() {
            // create a closure to access component in the callback below
            var self = this

            $.getJSON('/activateLensJson/' + this.id, function(data) {
                self.$parent.flash = true;
                self.$parent.lensname = data.name;
                self.$parent.active = data.active;
                self.$parent.flasherror = false;
            });
        }
    }
});

Vue.component('lensdeletion', {
    template: `
        <button id="delete" type="button" class="btn btn-sm btn-link" @click="deleteLens">
            <i class="far fa-trash-alt"></i>
        </button>
    `,
    props: {
        deleteid: { },
        name: { },
    },
    methods:{
        deleteLens() {
            // create a closure to access component in the callback below
            var self = this

            self.$parent.flasherror = true;
            self.$parent.flash = false;
            self.$parent.lensname = this.name;

            $.getJSON('/deleteLensJson/' + this.deleteid);
        }
    }
});

new Vue({
    el: '#lens',
    data: {
        flash: false,
        flasherror: false,
        lensname: '',
        active: ''
    }
})

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>