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

    selectedMediafiles: [],
    uploadsVaultFolder: null,
  }), // state

  getters: {
    vault_pkid(state) {
      return (!state.myVault) ? null : state.myVault.id
    },
    vaultfolder_pkid(state) {
      return (!state.vaultRootFolder) ? null : vaultRootFolder.id
    },
  }, // getters

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

    // Pushes mediafile or array of mediafiles onto selectedMediafiles
    ADD_SELECTED_MEDIAFILES(state, payload) {
      if (!payload) {
        return
      }
      // If property id or filepath is set this is single mediafile
      if (payload['id'] || payload['filepath']) {
        state.selectedMediafiles.push({
          ...payload,
          type: payload.type || payload.mimetype,
        })
        return
      }
      for (var item of payload) {
        state.selectedMediafiles.push({
          ...item,
          type: item.type || item.mimetype,
        })
      }
    },

    // Sets the selected media files
    UPDATE_SELECTED_MEDIAFILES(state, payload) {
      state.selectedMediafiles = payload
    },

    REMOVE_SELECTED_MEDIAFILE_BY_INDEX(state, index) {
      Vue.delete(state.selectedMediafiles, index)
    },

    UPDATE_UPLOADS_VAULT_FOLDER(state, payload) {
      state.uploadsVaultFolder = payload
    },

    // Clears out the selected media files
    CLEAR_SELECTED_MEDIAFILES(state) {
      state.selectedMediafiles = []
    },

  }, // mutations

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

  }, // actions
}

export default vault
