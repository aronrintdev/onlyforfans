/**
 * js/store/posts.js
 */
import _ from 'lodash'
import axios from 'axios'
import Vue from 'vue'
import propSelect from '@helpers/propSelect'


const route = window.route

export const posts = {
  namespaced: true,

  state: () => ({
    /** Post cache */
    posts: {},
  }),

  getters: {},

  mutations: {
    UPDATE_POST(state, payload) {
      const post = propSelect(payload, 'post')
      Vue.set(state.posts, post.id, post)
    },
  },

  actions: {

    /** Gets Post from cache, or loads from server */
    getPost({ dispatch, state }, id) {
      return new Promise((resolve, reject) => {
        if (state.posts[id]) {
          resolve(state.posts[id])
        } else {
          dispatch('updatePost', id)
            .then(val => resolve(val))
            .catch(err => reject(err))
        }
      })
    },

    /** Loads a post from the server */
    updatePost({ commit, state }, id) {
      return new Promise((resolve, reject) => {
        axios.get(route('posts.show', { post: id }))
          .then(response => {
            commit('UPDATE_POST', response.data)
            resolve(state.posts[id])
          })
          .catch(error => reject(error))
      })
    }
  },
}


export default posts
