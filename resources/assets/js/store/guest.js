/**
 * Guest Application Vuex Store
 */
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({

  state: {
    mobile: false,
    screenSize: 'xs',
  },

  getters: {},

  mutations: {
    UPDATE_MOBILE(state, payload) {
      state.mobile = payload
    },

    UPDATE_SCREEN_SIZE(state, payload) {
      state.screenSize = payload
    },
  },

  actions: {},

})
