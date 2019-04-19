<?php echo NoCaptcha::renderJs(LaravelGettext::getLocaleLanguage()); ?>


<?php $__env->startSection('title'); ?>
    <?php echo e(_i("Register")); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?php echo e(_i('Register')); ?></div>

                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('register')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Full Name')); ?></label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control<?php echo e($errors->has('name') ? ' is-invalid' : ''); ?>" name="name" value="<?php echo e(old('name')); ?>" required autofocus>

                                <?php if($errors->has('name')): ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($errors->first('name')); ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('E-Mail Address')); ?></label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control<?php echo e($errors->has('email') ? ' is-invalid' : ''); ?>" name="email" value="<?php echo e(old('email')); ?>" required>

                                <?php if($errors->has('email')): ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($errors->first('email')); ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Password')); ?></label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control<?php echo e($errors->has('password') ? ' is-invalid' : ''); ?>" name="password" required>

                                <?php if($errors->has('password')): ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($errors->first('password')); ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Confirm Password')); ?></label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="country" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Country of residence')); ?></label>

                            <div class="col-md-6">
                                <select class="form-control" id="country" name="country">
                                    <option value="">&nbsp;</option>
                                    <?php $__currentLoopData = Countries::getList(LaravelGettext::getLocaleLanguage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option <?php if($code == old('country')): ?> selected="selected"<?php endif; ?> value="<?php echo e($code); ?>"><?php echo e($country); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="observationlanguage" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Standard language for observations')); ?></label>

                            <div class="col-md-6">
                                <select class="form-control" id="observationlanguage" name="observationlanguage">
                                    <option value="">&nbsp;</option>
                                    <?php $__currentLoopData = Languages::lookup('major', LaravelGettext::getLocaleLanguage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(old('observationlanguage') != ''): ?>
                                            <option value="<?php echo e($code); ?>"<?php if($code == old('observationlanguage')): ?> selected="selected"<?php endif; ?>><?php echo e(ucfirst($language)); ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo e($code); ?>"<?php if($code == LaravelGettext::getLocaleLanguage()): ?> selected="selected"<?php endif; ?>><?php echo e(ucfirst($language)); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="language" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Language for user interface')); ?></label>

                            <div class="col-md-6">
                                <select class="form-control" id="language" name="language">
                                    <option value="">&nbsp;</option>
                                    <?php $__currentLoopData = Config::get('laravel-gettext.supported-locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $localeText = ucwords(Locale::getDisplayLanguage($locale, LaravelGettext::getLocale()));
                                        ?>
                                        <?php if(old('language') != ''): ?>
                                            <option value="<?php echo e($locale); ?>"<?php if($locale == old('language')): ?> selected="selected"<?php endif; ?>><?php echo e($localeText); ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo e($locale); ?>"<?php if($locale == LaravelGettext::getLocale()): ?> selected="selected"<?php endif; ?>><?php echo e($localeText); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" name="license" id="license">
                            <label for="cclicense" class="col-md-4 col-form-label text-md-right"><?php echo e(_i("License for drawings")); ?></label>

                            <div class="col-md-6">
                                <select name="cclicense" id="cclicense" onchange="enableDisableCopyright();" class="form-control">
                                    <option value="0" <?php if(0 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution CC BY</option>
                                    <option value="1" <?php if(1 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution-ShareAlike CC BY-SA</option>
                                    <option value="2" <?php if(2 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution-NoDerivs CC BY-ND</option>
                                    <option value="3" <?php if(3 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution-NonCommercial CC BY-NC</option>
                                    <option value="4" <?php if(4 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>
                                    <option value="5" <?php if(5 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>>Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>
                                    <option value="6" <?php if(6 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>><?php echo e(_i("No license (Not recommended!)")); ?></option>
                                    <option value="7" <?php if(7 == old('cclicense')): ?> selected="cclicense"<?php endif; ?>><?php echo e(_i("Enter your own copyright text")); ?></option>
                                </select>
                                <span class="help-block">
                                    <?php
                                        // Use the correct language for the chooser tool
                                        echo _i('It is important to select the correct license for your drawings!
                                            For help, see the %sCreative Commons license chooser%s.',
                                            '<a href="http://creativecommons.org/choose/?lang=' . LaravelGettext::getLocale() . '">', '</a>');
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="copyright" class="col-md-4 col-form-label text-md-right"><?php echo e(_i('Copyright notice')); ?></label>

                            <div class="col-md-6">
                                <input id="copyright" type="text" class="form-control" maxlength="128" name="copyright" value="<?php echo e(old('copyright')); ?>" >
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right"></label>
                            <div class="col-md-6">
                                <?php echo NoCaptcha::display(); ?>

                            </div>
                        </div>

                        <?php echo _i("Your personal information will be processed in accordance with the %sprivacy policy%s and shall be used only for user management and to keep you informed about our activities.", "<a href='/privacy'>", "</a>") . "<br /><br />";
                        ?>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-5">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo e(_i('Register')); ?>

                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>

$(document).ready(function()  {
    // Also put the correct copyright in the copyright field
    e = document.getElementById("cclicense");

    if (e.selectedIndex == 6) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = '';
    } else if (e.selectedIndex != 7) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = e.options[e.selectedIndex].text;
    } else {
        document.getElementById("copyright").readOnly=false;
    }
} );

$('#password').password({
    shortPass: '<?php echo _i("The password is too short"); ?>',
    badPass: '<?php echo _i("Weak; try combining letters & numbers"); ?>',
    goodPass: '<?php echo _i("Medium; try using special characters"); ?>',
    strongPass: '<?php echo _i("Strong password"); ?>',
    containsUsername: '<?php echo _i("The password contains the username"); ?>',
    enterPass: '<?php echo _i("Type your password"); ?>',
    showText: true, // shows the text tips
    animate: true, // whether or not to animate the progress bar on input blur/focus
    animateSpeed: 'fast', // the above animation speed
    username: false, // select the username field (selector or jQuery instance) for better password checks
    usernamePartialMatch: true, // whether to check for username partials
    minimumLength: 6 // minimum password length (below this threshold, the score is 0)
  });

function enableDisableCopyright() {
    var selectBox = document.getElementById("cclicense");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
    if (selectedValue == 7) {
        document.getElementById("copyright").readOnly=false;
        document.getElementById("copyright").value = '';
    } else if (selectedValue == 6) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = '';
    } else {
        document.getElementById("copyright").readOnly=true;
        // Use the old values to enable or disable the field at pageload
        e = document.getElementById("cclicense");
        document.getElementById("copyright").value = e.options[e.selectedIndex].text;
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>