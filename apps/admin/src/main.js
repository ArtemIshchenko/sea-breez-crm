// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
// import 'core-js/es6/promise'
// import 'core-js/es6/string'
// import 'core-js/es7/array'
import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import App from './App'
import router from './router'
import store from './store'
import { library } from '@fortawesome/fontawesome-svg-core'
import Vuelidate from 'vuelidate'
import Notifications from 'vue-notification'
import { constants, params, icons, http } from './config'

// Set plugins
Vue.use(BootstrapVue)
Vue.use(Vuelidate)
Vue.use(Notifications)

// Set global properties
Vue.prototype.$params = params
Vue.prototype.$const = constants

// Set notifying shorthand methods
function normalizeNotificationParams (params) {
  if (typeof params !== 'object') {
    params = { text: String(params) }
  }
  return params
}
Vue.prototype['$notifyError'] = function (params) {
  params = normalizeNotificationParams(params)
  this.$notify(Object.assign({
    type: 'error',
    title: this.$t('project', 'Ошибка'),
    duration: 10000
  }, params))
}
Vue.prototype['$notifySuccess'] = function (params) {
  params = normalizeNotificationParams(params)
  this.$notify(Object.assign({
    type: 'success',
    duration: 5000
  }, params))
}
Vue.prototype['$notifyWarning'] = function (params) {
  params = normalizeNotificationParams(params)
  this.$notify(Object.assign({
    type: 'warn',
    duration: 5000
  }, params))
}

// Set icons set
library.add(icons)

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: {
    App
  },
  created () {
    this.initHttpClient()
  },
  methods: {
    initHttpClient () {
      this.$store.commit('http/setClient', http)
      Vue.prototype.$http = this.$store.state.http.client
    }
  }
})
