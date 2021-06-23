/**
 * resources/assets/js/store/messaging/index.js
 */
import _ from 'lodash'

import ContactsModule from './contacts'

const route = window.route

export const messaging = {
  namespaced: true,

  modules: {
    contacts: ContactsModule,
  },

  state: () => ({}),

  getters: {},

  mutations: {},

  actions: {},
}

export default messaging
