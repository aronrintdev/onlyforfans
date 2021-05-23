/**
 * js/store/payouts.js
 * Vuex store module for payout items
 */
import Vue from 'vue'
import _ from 'lodash'
import axios from 'axios'


export const payouts = {
  namespaced: true,

  state: () => ({
    accounts: {},
  }),

  getters: {},

  mutations: {},

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

  },
}

export default payouts
