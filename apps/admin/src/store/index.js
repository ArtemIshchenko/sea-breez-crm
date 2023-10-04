import Vue from 'vue'
import Vuex from 'vuex'
import { app, http, user, page, gear } from '@/../../shared/store/modules'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    app,
    http,
    user,
    page,
    gear
  }
})
