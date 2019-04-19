
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

require( 'jszip' );
var pdfMake = require('pdfmake/build/pdfmake.js');
var pdfFonts = require('pdfmake/build/vfs_fonts.js');
pdfMake.vfs = pdfFonts.pdfMake.vfs;
require( 'datatables.net' );
require( 'datatables.net-bs4' );
require( 'datatables.net-buttons/js/buttons.colVis.js' );
require( 'datatables.net-buttons/js/buttons.html5.js' );
require( 'datatables.net-colreorder-bs4' );
require( 'datatables.net-buttons/js/buttons.print.js' );
require( 'datatables.net-plugins/sorting/natural.js');

require( 'password-strength-meter/dist/password.min.js' );

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

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
