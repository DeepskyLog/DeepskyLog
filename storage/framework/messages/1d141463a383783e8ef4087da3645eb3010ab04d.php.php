<?php $__env->startSection('title', _i('Settings')); ?>

<?php $__env->startSection('content'); ?>

<h3>Settings for <?php echo e($user->name); ?></h3>

<form role="form" action="/user/settings/<?php echo e($user->id); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PATCH'); ?>

    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active nav-item">
            <a class="nav-link active" href="#info" data-toggle="tab">
                <?php echo e(_i("Personal")); ?>

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#observingDetails" data-toggle="tab">
                <?php echo e(_i("Observing")); ?>

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#atlases" data-toggle="tab">
                <?php echo e(_i("Atlases")); ?>

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#languages" data-toggle="tab">
                <?php echo e(_i("Languages")); ?>

            </a>
        </li>
    </ul>

    <div id="my-tab-content" class="tab-content">
        <!-- Personal tab -->
        <div class="tab-pane active" id="info">

            <br />
            <label class="col-form-label"> <?php echo e(_i("Change profile picture")); ?></label>
            <input type="file" name="fileToUpload" id="filepond" class="filepond">
            Test Personal

        </div>

        <!-- Observing tab -->
        <div class="tab-pane" id="observingDetails">
            <br />
            Test observing
        </div>

        <!-- Atlasses tab -->
        <div class="tab-pane" id="atlases">
            <br />
            Test atlases
        </div>

        <div class="tab-pane" id="languages">
            <br />
            Test languages
        </div>
    </div>

    <input type="submit" class="btn btn-success" name="add" value="<?php echo e(_i("Update")); ?>" />
</form>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
<script>
$(function(){

    // First register any plugins
    $.fn.filepond.registerPlugin(FilePondPluginFileValidateType);
    $.fn.filepond.registerPlugin(FilePondPluginImageExifOrientation);
    $.fn.filepond.registerPlugin(FilePondPluginImagePreview);
    $.fn.filepond.registerPlugin(FilePondPluginImageCrop);
    $.fn.filepond.registerPlugin(FilePondPluginImageResize);
    $.fn.filepond.registerPlugin(FilePondPluginImageTransform);

    $.fn.filepond.setDefaults({
        acceptedFileTypes: ['image/*']
    });

    // Turn input element into a pond
    $('.filepond').filepond();
    
    // Listen for addfile event
    $('.filepond').on('FilePond:addfile', function(e) {
        console.log('file added event', e);
        // TODO: Set the file to the $_POST of $_FILES (request->...)
    });

    // Manually add a file using the addfile method
    $('.filepond').first().filepond('addFile', '/user/getImage');
});


/*    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    FilePond.setOptions({
        acceptedFileTypes: ['image/*'],
        server: {
            url: '/user/upload',
            process: {
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            },
            revert: {
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            }

        }
    });
    const inputElement = document.querySelector('input[type="file"]');
    const pond = FilePond.create( inputElement, { files: [
        {
            // the server file reference
            source: '/user/getImage',
        }
    ] } );
    */
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>