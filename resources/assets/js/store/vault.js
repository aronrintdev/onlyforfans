/**
 * resources/assets/js/store/vault.js
 *
 * Vuex def for vault items
 */

import _ from 'lodash'
import Vue from 'vue'

const route = window.route

export const vault = {
  namespaced: true,

  state: () => ({
    myVault: null,
    vaultRootFolder: null,
    currentFolderId: null,
  }),

  getters: {
    vault_pkid(state) {
      return (!state.myVault) ? null : state.myVault.id
    },
    vaultfolder_pkid(state) {
      return (!state.vaultRootFolder) ? null : vaultRootFolder.id
    },
  },

  mutations: {
    UPDATE_CURRENT_FOLDER_ID(state, payload) {
      state.currentFolderId = payload
    },
    UPDATE_MY_VAULT(state, payload) {
      state.myVault = payload
    },
    UPDATE_VAULT_ROOT_FOLDER(state, payload) {
      state.vaultRootFolder = payload
    },
  },

  actions: {
    loadVaultDashboard({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('vault.dashboard'))
          .then(response => {
            commit('UPDATE_MY_VAULT', response.data.myVault)
            commit('UPDATE_VAULT_ROOT_FOLDER', response.data.vaultRootFolder)
            resolve()
          })
          .catch(error => reject(error))
      })
    },
  },
}

export default vault
