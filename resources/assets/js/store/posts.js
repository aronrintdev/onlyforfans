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
    publicPosts: [],
  }),

  getters: {},

  mutations: {
    UPDATE_POST(state, payload) {
      const post = propSelect(payload, 'post')
      Vue.set(state.posts, post.id, post)
    },
    UPDATE_PUBLIC_POST(state, payload) {
      const post = propSelect(payload, 'post')
      const temp = state.publicPosts;
      const idx = temp.findIndex(p => p.post.id == post.id);
      if (idx > -1) {
        temp[idx] = {
          ...temp[idx],
          post: post,
        };
        state.publicPosts = [...temp];
      }
    },
    SET_PUBLIC_POSTS(state, payload) {
      state.publicPosts = payload
    }
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
