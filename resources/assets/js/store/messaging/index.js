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
    state.threads[threadId] = { meta: {}, messages: {}, gallery: {}, galleryMeta: {} }
  }
}

export const messaging = {
  namespaced: true,

  modules: {
    contacts: ContactsModule,
  },

  /* ------------------------------------------------------------------------ */
  /*                                   STATE                                  */
  /* ------------------------------------------------------------------------ */
  state: () => ({
    // Threads Structure
    // threads: {
    //   'threadId': {
    //     messages: {
    //       'messageId': {},
    //     },
    //     gallery: {},
    //     meta: {},
    //     galleryMeta: {},
    //   },
    // }
    threads: {},

    threadMeta: {},

    searchQuery: '',
  }),

  /* ------------------------------------------------------------------------ */
  /*                                  GETTERS                                 */
  /* ------------------------------------------------------------------------ */
  getters: {
    thread: state => threadId => {
      return state.threads[threadId]
    },

    galleryItems: state => threadId => {
      if (!state.threads[threadId] || !state.threads[threadId].gallery) {
        return []
      }
      return _.orderBy(state.threads[threadId].gallery, 'created_at', 'desc')
    },
    meta: state => threadId => {
      if (!state.threads[threadId] || !state.threads[threadId].meta) {
        return {}
      }
      return state.threads[threadId].meta
    },
    galleryMeta: state => threadId => {
      if (!state.threads[threadId] || !state.threads[threadId].galleryMeta) {
        return {}
      }
      return state.threads[threadId].galleryMeta
    },
  },

  /* ------------------------------------------------------------------------ */
  /*                                 MUTATIONS                                */
  /* ------------------------------------------------------------------------ */
  mutations: {

    UPDATE_THREADS(state, payload) {
      console.log('UPDATE_THREADS', { payload })
      for (var thread of payload.data) {
        prepThread(state, thread.id)
        state.threads[thread.id] = {
          ...state.threads[thread.id],
          ...thread,
        }
      }
      state.threadMeta = payload.meta
    },

    UPDATE_THREAD(state, payload) {
      Vue.set(state.threads, payload.id, { ...state.threads[payload.id], ...payload })
    },

    UPDATE_GALLERY(state, { threadId, payload }) {
      console.log('UPDATE_GALLERY', { threadId, payload })
      prepThread(state, threadId)
      if (payload.meta) {
        state.threads[threadId].galleryMeta = payload.meta || {}
      }

      // Add media files to threads object
      state.threads[threadId].gallery = {
        ...state.threads[threadId].gallery,
        ..._.keyBy(payload.data.map(
          o => ({ ...o, meta: { page: payload.meta ? payload.meta.current_page : 0 } })),
          'id'
        ),
      }
    },

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

  /* ------------------------------------------------------------------------ */
  /*                                  ACTIONS                                 */
  /* ------------------------------------------------------------------------ */
  actions: {
    getThread({ commit }, threadId) {
      return new Promise((resolve, reject) => {
        axios.get(route('chatthreads.show', { chatthread: threadId }))
          .then(response => {
            commit('UPDATE_THREAD', response.data)
            resolve(response)
          })
          .catch(error => reject(error))
      })
    },


    /**
     * Gets a page of chatthreads from the server
     */
    getThreads({ commit }, { page, take, filters, sortBy }) {
      return new Promise((resolve, reject) => {
        console.log('getThreads', { page, take, filters, sortBy })
        let params = { page, take }
        params = { ...params, ...filters }
        if ( this.sortBy ) {
          params.sortBy = this.sortBy
        }
        axios.get(route('chatthreads.index'), { params })
          .then(response => {
            commit('UPDATE_THREADS', response.data)
            resolve(response)
          })
          .catch(error => reject(error))
      })
    },

    searchThreads({ commit }, { q }) {
      return new Promise((resolve, reject) => {
        console.log('searchThreads', { q })
        axios.get(route('chatthreads.index'), { params: { q } })
          .then(response => {
            commit('UPDATE_THREADS', response.data)
            resolve(response)
          })
          .catch(error => reject(error))
      })
    },

    getGallery({ commit }, { chatthread, page, take }) {
      console.log('getMessages', {chatthread, page, take})
      return new Promise((resolve, reject) => {
        axios.get(route('chatthreads.gallery', chatthread), {
          params: { take, page }
        }).then(response => {
          commit('UPDATE_GALLERY', { threadId: chatthread, payload: response.data })
          resolve(response.data)
        }).catch(error => reject(error))
      })
    },
    getMessages({ commit }, {threadId, page, take }) {
      console.log('getMessages', {threadId, page, take})
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
