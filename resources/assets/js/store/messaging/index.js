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

    /**
     * Selected Media files for the message form
     */
    selectedMediafiles: [],

    uploadsVaultFolder: null,
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

    /** Pushes mediafile or array of mediafiles onto selectedMediafiles */
    ADD_SELECTED_MEDIAFILES(state, payload) {
      if (!payload) {
        return
      }
      // If property id or filepath is set this is single mediafile
      if (payload['id'] || payload['filepath']) {
        state.selectedMediafiles.push(payload)
        return
      }
      for (var item of payload) {
        state.selectedMediafiles.push(item)
      }
    },

    /** Sets the selected media files */
    UPDATE_SELECTED_MEDIAFILES(state, payload) {
      state.selectedMediafiles = payload
    },

    REMOVE_SELECTED_MEDIAFILE_BY_INDEX(state, index) {
      Vue.delete(state.selectedMediafiles, index)
    },

    UPDATE_UPLOADS_VAULT_FOLDER(state, payload) {
      state.uploadsVaultFolder = payload
    },

    /** Clears out the selected media files */
    CLEAR_SELECTED_MEDIAFILES(state) {
      state.selectedMediafiles = []
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
    getUploadsVaultFolder({ commit, state }) {
      return new Promise((resolve, reject) => {
        if (state.uploadsVaultFolder) {
          resolve(state.uploadsVaultFolder)
          return
        }
        axios.get(route('vaultfolders.uploads-folder', { type: 'message' }))
          .then(response => {
            commit('UPDATE_UPLOADS_VAULT_FOLDER', response.data)
            resolve(state.uploadsVaultFolder)
          })
          .catch(error => reject(error))
      })
    }
  },
}

export default messaging
