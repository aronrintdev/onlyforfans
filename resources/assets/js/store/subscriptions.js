/**
 * js/store/subscriptions.js
 *
 * Vuex store related to subscriptions
 */

/* Imports */
import _, { kebabCase } from 'lodash'
import axios from 'axios'
import propSelect from '@helpers/propSelect'
import Vue from 'vue'

/**
 * Ziggy Routing
 *
 * TODO: move to recompiled before 1.0 release
 */
const route = window.route

const emptySubscription = {
  id: '',
  type: 'timelines',
  for: {
    id: '',
    name: '',
  },
  period: '',
  period_interval: '',
  price: 0,
  currency: 'USD',
  access_level: 'premium',
  active: false,
}

export const subscriptions = {
  namespaced: true,

  /* --------------------------------- STATE -------------------------------- */
  state: () => ({
    /** Count of user's active and inactive subscriptions */
    count: {
      active: 0,
      inactive: 0,
    },

    /**
     * List of active Subscriptions
     */
    active: {},

    /**
     * List of inactive subscriptions
     */
    inactive: {},
  }),

  /* -------------------------------- GETTERS ------------------------------- */
  getters: {
    /** Returns a empty subscription object */
    emptySubscription() {
      return emptySubscription
    },

    /** Attempt to get a subscription by id */
    getSubscriptionById: (state) => (id) => {
      if (state.active[id]) {
        return state.active[id]
      }
      if (state.inactive[id]) {
        return state.inactive[id]
      }
      return null
    },
  },

  /* ------------------------------- MUTATIONS ------------------------------ */
  mutations: {
    UPDATE_COUNT(state, payload) {
      state.count = payload
    },

    UPDATE_ACTIVE(state, payload) {
      state.active = {
        ...state.active,
        ...(_.keyBy(propSelect(payload, 'subscriptions'), 'id')),
      }
    },

    UPDATE_INACTIVE(state, payload) {
      state.inactive = {
        ...state.inactive,
        ...(_.keyBy(propSelect(payload, 'subscriptions'), 'id')),
      }
    },

    UPDATE_SUBSCRIPTION(state, payload) {
      const subscription = propSelect(payload, 'subscription')
      if (subscription.active) {
        Vue.set(state.active, subscription.id, subscription)
        Vue.delete(state.inactive, subscription.id)
      } else {
        Vue.set(state.inactive, subscription.id, subscription)
        Vue.delete(state.active, subscription.id)
      }
    },
  },

  /* -------------------------------- ACTIONS ------------------------------- */
  actions: {
    updateCount({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('subscriptions.count')).then(response => {
          commit('UPDATE_COUNT', response.data)
          resolve()
        }).catch(error => reject(error))
      })
    },

    getActive({ commit }, { page, limit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('subscriptions.index'), { params: { page: page, limit: limit, is_active: 1 } })
          .then(response => {
            commit('UPDATE_ACTIVE', response.data)
            resolve()
          })
          .catch(error => reject(error))
      })
    },

    getInactive({ commit }, { page, limit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('subscriptions.index'), { params: { page: page, limit: limit, is_active: 0 } })
          .then(response => {
            commit('UPDATE_INACTIVE', response.data)
            resolve()
          }).catch(error => reject(error))
      })
    },

    getSubscription({ commit }, id) {
      return new Promise((resolve, reject) => {
        axios.get(route('subscriptions.show', { subscription: id }))
          .then(response => {
            commit('UPDATE_SUBSCRIPTION', response.data)
            resolve()
          }).catch(error => reject(error))
      })
    },

    cancelSubscription({ commit }, subscription) {
      return new Promise((resolve, reject) => {
        axios.post(route('subscriptions.cancel', { subscription: subscription.id }))
          .then(response => {
            commit('UPDATE_SUBSCRIPTION', response.data)
            resolve(response)
          }).catch(error => reject(error))
      })
    },

  },
}

export default subscriptions
