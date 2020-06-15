
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

require('select2');

import $ from 'jquery';
window.$ = window.jQuery = require( 'jquery' );

import 'jquery-ui/ui/widgets/datepicker.js';

require( 'datatables.net' );
require( 'datatables.net-bs4' );
require( 'datatables.net-buttons/js/buttons.colVis.js' );
require( 'datatables.net-buttons/js/buttons.html5.js' );
require( 'datatables.net-colreorder-bs4' );
require( 'datatables.net-buttons/js/buttons.print.js' );
require( 'datatables.net-plugins/sorting/natural.js');

import jsZip from 'jszip';

// This line was the one missing
window.JSZip = jsZip;

var pdfMake = require('pdfmake/build/pdfmake.js');
var pdfFonts = require('pdfmake/build/vfs_fonts.js');
pdfMake.vfs = pdfFonts.pdfMake.vfs;

require( 'password-strength-meter/dist/password.min.js' );

// bootstrap-fileinput
require( 'popper.js/dist/umd/popper.min.js' );
require( 'bootstrap-fileinput/js/fileinput.min.js');
require( 'bootstrap-fileinput/themes/fas/theme.min.js');

// Lity
require( 'lity/dist/lity.js');

// Highcharts
var Highcharts = require('highcharts/highcharts.js');
// Load module after Highcharts is loaded
require('highcharts/modules/exporting')(Highcharts);

// Enable turoblinks
//var Turbolinks = require("turbolinks")
//Turbolinks.start()
