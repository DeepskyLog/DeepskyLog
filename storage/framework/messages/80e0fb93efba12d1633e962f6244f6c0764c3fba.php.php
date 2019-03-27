<li>
    <form role="form" action="/lang" method="POST">
        <?php echo e(csrf_field()); ?>

        <select class="form-control" name="language" onchange="this.form.submit()">
            <?php $__currentLoopData = Config::get('laravel-gettext.supported-locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $localeText = ucwords(Locale::getDisplayLanguage($locale, $locale));
                ?>
                <option value="<?php echo e($locale); ?>" <?php if($locale == LaravelGettext::getLocale()): ?>
                    selected="selected"
                <?php endif; ?>><?php echo e($localeText); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>
</li>
<br />
