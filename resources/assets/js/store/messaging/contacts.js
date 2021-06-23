/**
 * resources/assets/js/store/messaging/contacts.js
 */

import _ from 'lodash'
import Vue from 'vue'
import contains from '@helpers/contains'

const route = window.route

const cacheKeyFor = function({ filter, sort, take }) {
  return `f[${filter}]sb[${sort}].t[${take}]`
}

export const contacts = {
  namespaced: true,

  state: () => ({

    contacts: {},

    /** Stores page filter and metadata information to avoid multiple calls to server */
    cache: {},

    pinned: [
      'all',
      'subscribers',
      'followers',
    ],

    filters: {
      all: {
        key: 'all',
        always: true,
        rules: [],
      },
      subscribers: {
        key: 'subscribers',
        rules: ['is_subscriber'],
        extraFilters: [
          'totalSpent'
          // 'subscribed-over'
        ],
      },
      followers: {
        key: 'followers',
        rules: ['is_follower'],
        // extraFilters: [ 'following-for' ],
      },
      canceled: {
        key: 'canceled',
        rules: ['is_cancelled_subscriber'],
        // extraFilters: [ 'cancelled-ago' ],
      },
      expired: {
        key: 'expired',
        rules: ['is_expired_subscriber'],
        // extraFilters: [ 'expired-ago' ],
      },
      purchasers: {
        key: 'purchasers',
        rules: ['has_purchased_post'],
        extraFilters: [
          'totalSpent'
        ]
      },
      tippers: {
        key: 'tippers',
        rules: ['has_tipped'],
        extraFilters: [
          'totalSpent',
          // 'totalTipped',
        ]
      },
    },

  }),

  getters: {
    /** Gets filter object for a filter, take, sort, and page */
    getContactsFor: state => ({ filter, page, take, sort }) => {
      const cacheKey = cacheKeyFor({ filter, take, sort })
      if (typeof state.cache[cacheKey] === 'undefined') {
        return null
      }
      const filterList = state.cache[cacheKey][page]
      // Return filter of contacts
      return _.filter(state.contacts, o => contains(filterList, o.id))
    },

    /** Gets filtered object for a filter, take, sort containing all pages loaded */
    getAllPagesContacts: state => ({ filter, take, sort }) => {
      const cacheKey = cacheKeyFor({ filter, take, sort })
      if (typeof state.cache[cacheKey] === 'undefined') {
        return null
      }
      // Filter out meta data
      var filterList = _.filter(state.cache[cacheKey], (page, key) => {
        return key !== 'meta'
      })
      // Flatten Pages
      filterList = _.flatten(filterList)
      // Return filter of contacts
      return _.filter(state.contacts, o => contains(filterList, o.id))
    },

    /** List of pinned filters */
    pinnedFilters: state => {
      return _.filter(state.filters, o => contains(state.pinned, o.key))
    },

    contactsCount: state => {
      return Object.keys(state.contacts).length || 0
    },

    /** List of selected contacts */
    selectedContacts: state => {
      return _.filter(state.contacts, o => o.selected)
    },

    /** Count of selected contacts */
    selectedContactsCount: (state, getters) => {
      if (!getters.selectedContacts) {
        return 0
      }
      return Object.keys(getters.selectedContacts).length || 0
    },
  },

  mutations: {
    UPDATE_CONTACTS_PAGE(state, { data, page, take, filter, sort }) {
      // Filters besides 'all' should select all by default
      const selected = filter !== 'all' ? true : false

      // Save contacts to contacts object indexed by id
      state.contacts = { ...state.contacts, ..._.keyBy(data.data.map(o => ({ ...o, selected })), 'id') }

      // Get cache key location
      const cacheKey = cacheKeyFor({ filter, take, sort })

      // Initialize cache location if needed
      if (typeof state.cache[cacheKey] === 'undefined') {
        state.cache[cacheKey] = {}
      }
      // Store meta data
      state.cache[cacheKey].meta = data.meta
      state.cache[cacheKey][page] = data.data.map(o => o.id)
    },

    /** Adds/updates contacts to saved list */
    SAVE_CONTACTS_LIST(state, payload) {
      state.contacts = { ...state.contacts, ..._.keyBy(payload.data.map(o => ({ ...o, selected: false })), 'id') }
    },

    /** Sets items given to be selected */
    SELECT_CONTACTS(state, payload) {
      // Select items in payload
      _.each(payload, o => Vue.set(state.contacts, o.id, { ...o, selected: true }))
    },

    UNSELECT_ALL(state, payload) {
      const selected = _.filter(state.contacts, o => o.selected)
      _.each(selected, o => Vue.set(state.contacts, o.id, { ...o, selected: false }))
    },

    /** Update contents of a contact */
    UPDATE_CONTACT(state, payload) {
      Vue.set(state.contacts, payload.id, payload)
    }
  },

  actions: {
    loadContacts({ state, commit }, { page, take, filter, sort }) {
      return new Promise((resolve, reject) => {
        let params = { page, take }
        for (var filterRule of state.filters[filter].rules) {
          params[filterRule] = 1
        }
        if (sort) {
          params.sortBy = sort
        }
        axios.get(route('mycontacts.index'), { params })
          .then(response => {
            commit('UPDATE_CONTACTS_PAGE', { data: response.data, page, take, filter, sort })
            resolve()
          })
          .catch(error => reject(error))
      })
    },

  },
}

export default contacts
