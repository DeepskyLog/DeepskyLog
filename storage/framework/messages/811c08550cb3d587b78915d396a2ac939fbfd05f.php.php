<h1>Test</h1>

<?php echo e(_i('Translated string')); ?>


<?php echo LaravelGettext::getSelector([
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',
    'nl' => 'Nederlands',
    'sv' => 'Svenska',
    'fr' => 'Français'
])->render();; ?>


<?php echo $__env->make("layout.master", \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>