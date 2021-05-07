/**
 * js/store/earnings.js
 */
import _ from 'lodash'
import Vue from 'vue'
import axios from 'axios'
import propSelect from '@helpers/propSelect'

const route = window.route

const earnings = {
  namespaced: true,

  state: () => ({
    transactions: [],
    sums: {
      credits: {},
      debits: {},
      from: null,
      to: null,
    },

  }),

  getters: {
    credits: state => state.sums.credits,
    debits: state => state.sums.debits,
  },

  mutations: {
    UPDATE_SUMS(state, payload) {
      state.sums = payload
    },
  },

  actions: {
    updateSums({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('earnings.index'))
          .then(response => {
            commit('UPDATE_SUMS', response.data)
            resolve()
          })
          .catch(err => reject(err))
      })
    },

  },

}


export default earnings
