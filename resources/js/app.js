
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

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

// Filepond
window.FilePondPluginImagePreview = require('filepond-plugin-image-preview');
window.FilePondPluginFileValidateType = require('filepond-plugin-file-validate-type');
window.FilePondPluginImageExifOrientation = require('filepond-plugin-image-exif-orientation');
window.FilePondPluginImageCrop = require('filepond-plugin-image-crop');
window.FilePondPluginImageResize = require('filepond-plugin-image-resize');
window.FilePondPluginImageTransform = require('filepond-plugin-image-transform');
window.FilePond = require('filepond/dist/filepond.min.js');
require("jquery-filepond/filepond.jquery.js");

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vue from 'vue';

// register globally
Vue.component('select2', {
    props: ['options', 'value'],
    watch: {
      value: function (value) {
        // update value
        $(this.$el)
            .val(value)
            .trigger('change')
      },
      options: function (options) {
        // update options
        $(this.$el).empty().select2({ data: options })
      }
    },
    mounted: function () {
      var vm = this
      $(this.$el)
        // init select2
        .select2({ data: this.options, theme: 'bootstrap', width: '100%', allowClear: true })
        .val(this.value)
        .trigger('change')
        // emit event on change.
        .on('select2:select', function () {
          vm.$emit('input', this.value)
        })
    },
    destroyed: function () {
      $(this.$el).off().select2('destroy')
    },
    template: '<select><slot></slot></select>'
  })
