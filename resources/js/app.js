
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import 'alpinejs';
const Choices = require('choices.js/public/assets/scripts/choices.js');

import $ from 'jquery';
window.$ = window.jQuery = require( 'jquery' );

import flatpickr from 'flatpickr';
import 'flatpickr/dist/l10n/nl.js';
import 'flatpickr/dist/l10n/es.js';
import 'flatpickr/dist/l10n/sv.js';
import 'flatpickr/dist/l10n/fr.js';
import 'flatpickr/dist/l10n/de.js';

var pdfMake = require('pdfmake/build/pdfmake.js');
var pdfFonts = require('pdfmake/build/vfs_fonts.js');
pdfMake.vfs = pdfFonts.pdfMake.vfs;

// Lity
require( 'lity/dist/lity.js');

// Highcharts
var Highcharts = require('highcharts/highcharts.js');
// Load module after Highcharts is loaded
require('highcharts/modules/exporting')(Highcharts);
