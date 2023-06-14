require('./bootstrap');

import Vue from "vue"

import Multiselect from 'vue-multiselect'
import VueCurrencyFilter from 'vue-currency-filter'
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import AnimatedNumber from "animated-number-vue";
import 'animate.css';

Vue.use(VueCurrencyFilter,{
    symbol: '',
    thousandsSeparator: '.',
    fractionCount: 0,
    fractionSeparator: ',',
    symbolPosition: 'front',
    symbolSpacing: true
})
Vue.use(VueSweetalert2);

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('multiselect', Multiselect)
Vue.component('AnimatedNumber', AnimatedNumber);

const app = new Vue({
    el: '#app',
    methods: {
      formatToPrice(value) {
        return `${value.toFixed(2)}`;
      }
    }
});


