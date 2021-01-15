const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
	resolve: {
		modules: [
			'node_modules',
			__dirname + '/vendor/spatie/laravel-medialibrary-pro/resources/js',
		],
	},
});

mix.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css').options({
		postCss: [
			require('tailwindcss'),
		]});
mix.copy('node_modules/choices.js/public/assets/scripts/choices.js', 'public/js');
mix.copy('node_modules/popper.js/dist/popper.js.map', 'public/js');
mix.copy('node_modules/trix/dist/trix.js', 'public/js');
