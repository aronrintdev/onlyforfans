/**
 * js/store/banking.js
 * Vuex store module for payout items
 */
import Vue from 'vue'
import _, { reject } from 'lodash'
import axios from 'axios'
import propSelect from '@helpers/propSelect'

const route = window.route

export const banking = {
  namespaced: true,

  /* --------------------------------- STATE -------------------------------- */
  state: () => ({
    accounts: [],
  }),

  /* -------------------------------- GETTERS ------------------------------- */
  getters: {},

  /* ------------------------------- MUTATIONS ------------------------------ */
  mutations: {
    UPDATE_ACCOUNTS(state, payload) {
      state.accounts = propSelect(payload, 'accounts')
    }
  },

  /* -------------------------------- ACTIONS ------------------------------- */
  actions: {
    /**
     * Retrieves bank information from routingnumbers.info api
     * Message json response form:
     * ```
     * {
     *  office_code: string,
     *  institution_status_code: string,
     *  state: string,
     *  rn: string,
     *  new_routing_number: string,
     *  message: string,
     *  change_date: string,   // "MMDDYY"
     *  customer_name: string, // <== This is the bank name
     *  zip: string,
     *  telephone: string,
     *  city: string,
     *  data_view_code: string,
     *  record_type_code: string,
     *  address: string,
     *  code: number,
     *  routing_number: string,
     * }
     * ```
     */
    bankFromRoutingNumber({ commit }, routingNumber) {
      return new Promise((resolve, reject) => {
        fetch(`https://www.routingnumbers.info/api/data.json?rn=${routingNumber}`)
          .then(response => {
            resolve(response.json())
          })
          .catch(err => reject(err))
      })
    },

    removeAccount({ dispatch }, id) {
      return new Promise((resolve, reject) => {
        axios.delete(route('bank-accounts.destroy', { bank_account: id }))
          .then(response => {
            dispatch('updateAccounts', { page: 1, take: 20 })
              .then(response => resolve(response))
              .catch(error => reject(error))
          })
          .catch(error => reject(error))
      })
    },

    setDefault({ dispatch }, id) {
      return new Promise((resolve, reject) => {
        axios.put(route('bank-accounts.set-default'), { id })
          .then(response => {
            dispatch('updateAccounts', { page: 1, take: 20 })
              .then(response => resolve(response))
              .catch(error => reject(error))
          }).catch(error => reject(error))
      })
    },

    updateAccounts({ commit }, options = {}) {
      return new Promise((resolve, reject) => {
        let page = options.page || 1
        let take = options.take || 20
        axios.get(route('bank-accounts.index'), { params: { page, take } })
          .then(response => {
            commit('UPDATE_ACCOUNTS', response.data)
            resolve(response)
          }).catch(error => reject(error))
      })
    }

  },
}

export default banking
