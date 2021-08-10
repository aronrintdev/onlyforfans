/**
 * js/store/statements.js
 *
 * Vuex store for statements section
 */
import _ from 'lodash'
import axios from 'axios'
import propSelect from '@helpers/propSelect'

const route = window.route

export const statements = {
  namespaced: true,

  /* --------------------------------- STATE -------------------------------- */
  state: () => ({
    /**
     * Creator Balances
     */
    balances: {
      balances: { amount: 0, currency: 'USD' },
      pending: { amount: 0, currency: 'USD' },
    },

    /**
     * Summarized Totals
     */
    totals: {
      credits: {
        subscriptions: 0,
        purchases: 0,
        tips: 0,
      },
      debits: {
        fees: 0,
        chargebacks: 0,
        refunds: 0,
      },
    },

    /**
     * Earnings Summarized Totals
     */
    totals: {
      credits: {},
      debits: {},
      from: null,
      to: null,
    },

    /**
     * Transactions
     */
    transactions: [],


  }),

  /* -------------------------------- GETTERS ------------------------------- */
  getters: {
    credits: state => state.totals.credits,

    debits: state => state.totals.debits,

    /** Credit Total Amount */
    creditTotal: state => {
      return _.sumBy(_.toArray(state.totals.credits), 'total')
    },
    /** Debit Total Amount */
    debitTotal: state => {
      return _.sumBy(_.toArray(state.totals.debits), 'total')
    },
  },

  /* ------------------------------- MUTATIONS ------------------------------ */
  mutations: {
    UPDATE_BALANCES(state, payload) {
      state.balances = payload
    },
    UPDATE_TOTALS(state, payload) {
      state.totals = payload
    },
    UPDATE_TRANSACTIONS(state, payload) {
      state.transactions = propSelect(payload, 'transactions')
    },
  },

  /* -------------------------------- ACTIONS ------------------------------- */
  actions: {
    updateTotals({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('earnings.index'))
          .then(response => {
            commit('UPDATE_TOTALS', response.data)
            resolve(response)
          })
          .catch(err => reject(err))
      })
    },

    getBalances({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('earnings.balances'))
          .then(response => {
            commit('UPDATE_BALANCES', response.data)
            resolve(response)
          })
          .catch(error => reject(error))
      })
    },

    getTransactions({ commit }, { page, limit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('earnings.transactions'), { params: { take: this.take, page: this.page } })
          .then(response => {
            commit('UPDATE_TRANSACTIONS', response.data)
            resolve(response)
          }).catch(error => reject(error))
      })
    },
  },

}

export default statements
