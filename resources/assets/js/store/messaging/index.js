/**
 * resources/assets/js/store/messaging/index.js
 */
import _ from 'lodash'
import Vue from 'vue'

import ContactsModule from './contacts'

const route = window.route

/**
 * Preps thread object for a threadId
 * @param {*} state
 * @param {*} threadId
 */
const prepThread = function (state, threadId) {
  if (typeof state.threads[threadId] === 'undefined') {
    state.threads[threadId] = { meta: {}, messages: {} }
  }
}

export const messaging = {
  namespaced: true,

  modules: {
    contacts: ContactsModule,
  },

  state: () => ({
    // threads: {
    //   'threadId': {
    //     messages: {
    //       'messageId': {},
    //     },
    //   },
    // }
    threads: {},
  }),

  getters: {},

  mutations: {
    UPDATE_MESSAGES(state, { threadId, page, take, payload }) {
      prepThread(state, threadId)
      // Save Metadata from call
      state.threads[threadId].meta = payload.meta || {}

      // Adds messages to threads object
      state.threads[threadId].messages = {
        ...state.threads[threadId].messages,
        ..._.keyBy(payload.data.map(o => ({ ...o, meta: { page, take } })), 'id'),
      }
    },

    /**
     * Update a single message
     */
    UPDATE_MESSAGE(state, message) {
      prepThread(state, message.chatthread_id)
      Vue.set(state.threads[message.chatthread_id].messages, message.id, message)
    },
  },

  actions: {
    getMessages({ commit }, threadId, { page, take }) {
      return new Promise((resolve, reject) => {
        axios.get(route('chatmessages.index'), { params: {} })
          .then(response => {
            commit('UPDATE_MESSAGES', {
              threadId,
              page,
              take,
              payload: response.data,
            })
            resolve()
          })
          .catch(error => reject(error))
      })
    },
  },
}

export default messaging
