import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

/**
 * Routes from window, want to eventually deprecate and move to dedicated compiled routes
 */
const route = window.route

/**
 * Helps select items from response result set
 * @param {Object} payload - response payload
 * @param {String} propertyName - name of property
 */
const propSelect = (payload, propertyName, type = 'array') => {
  return payload.hasOwnProperty(propertyName)
    ? payload[propertyName]
    : payload.hasOwnProperty('data')
      ? payload.data
      : type === 'array'
        ? [] : {}
}

// Modules
import searchModule from './store/search'

export default new Vuex.Store({
  modules: {
    search: searchModule,
  },

  state: {
    vault: {},
    vaultfolder: {},
    breadcrumb: [],
    /**
     * shares for a vaultfolder, used to mark what resources session user *has* shared out
     */
    shares: [],
    /**
     * resources that have been shared with session user
     */
    shareables: [],
    /**
     * resources that session user has saved
     */
    saves: [],
    /**
     * Posts on the current open timeline
     */
    feeditems: [],
    feeddata: {},
    /**
     * Current open stories
     */
    stories: [],
    /**
     * Logged in user timeline
     */
    timeline: null,
    /**
     * Logged in user information
     */
    session_user: null,
    /**
     * UI Flags for logged in user
     */
    uiFlags: [],
    unshifted_timeline_post: null,
    is_loading: true,
  },

  mutations: {
    UPDATE_VAULT(state, payload) {
      state.vault = propSelect(payload, 'vault')
    },
    UPDATE_VAULTFOLDER(state, payload) {
      state.vaultfolder = propSelect(payload, 'vaultfolder')
    },
    UPDATE_BREADCRUMB(state, payload) {
      state.breadcrumb = propSelect(payload, 'breadcrumb')
    },
    UPDATE_SHARES(state, payload) {
      state.shares = propSelect(payload, 'shares')
    },
    UPDATE_SHAREABLES(state, payload) {
      state.shareables = propSelect(payload, 'shareables')
    },
    UPDATE_SAVES(state, payload) {
      state.saves = propSelect(payload, 'saves')
    },
    UPDATE_FEEDITEMS(state, payload) {
      state.feeditems = propSelect(payload, 'feeditems')
    },
    UPDATE_FEEDDATA(state, payload) {
      console.log('UPDATE_FEEDDATA', { payload, })
      state.feeddata = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_STORIES(state, payload) {
      state.stories = propSelect(payload, 'stories')
    },
    UPDATE_TIMELINE(state, payload) {
      state.timeline = propSelect(payload, 'timeline')
    },
    UPDATE_SESSION_USER(state, payload) {
      state.session_user = propSelect(payload, 'session_user')
    },
    UPDATE_UI_FLAGS(state, payload) {
      state.uiFlags = { ...state.uiFlags, ...propSelect(payload, 'uiFlags', 'object') }
    },
    UPDATE_UNSHIFTED_TIMELINE_POST(state, payload) {
      state.unshifted_timeline_post = propSelect(payload, 'post')
    },
    UPDATE_LOADING(state, payload) {
      state.is_loading = payload
    },
  },

  actions: {
    getVault({ commit }, id) {
      axios.get(route('valuts.show', { id }))
        .then((response) => {
          commit('UPDATE_VAULT', response.data)
          commit('UPDATE_LOADING', false)
        })
    },

    getVaultfolder({ commit }, id) {
      axios.get(route('vaultfolders.show', { id }))
        .then((response) => {
          commit('UPDATE_VAULTFOLDER', response.data)
          commit('UPDATE_BREADCRUMB', response.data)
          commit('UPDATE_SHARES', response.data)
          commit('UPDATE_LOADING', false)
        })
    },

    getShareables({ commit }) {
      axios.get(route('shareables.index'))
        .then((response) => {
          commit('UPDATE_SHAREABLES', response.data)
          commit('UPDATE_LOADING', false)
        })
    },

    getSaves({ commit }) {
      // Route does not exist
      const url = `/saved`
      axios.get(url).then((response) => {
        commit('UPDATE_SAVES', response.data)
        commit('UPDATE_LOADING', false)
      })
    },

    getFeeddata( { commit }, { timelineId, page, limit, isHomefeed } ) {
      const url = isHomefeed 
        ? `/timelines/home/feed?page=${page}&take=${limit}`
        : `/timelines/${timelineId}/feed?page=${page}&take=${limit}`;
      axios.get(url).then( (response) => {
        commit('UPDATE_FEEDDATA', response);
        commit('UPDATE_LOADING', false);
      });
    },

    getStories({ commit }, { filters }) {
      const username = this.state.session_user.username // Not used - This param will eventually be DEPRECATED
      const params = {}
      if (Object.keys(filters).includes('user_id')) {
        params.user_id = filters.user_id
      }
      axios.get(route('stories.index'), { params })
        .then((response) => {
          commit('UPDATE_STORIES', response.data)
          commit('UPDATE_LOADING', false)
        })
    },

    unshiftPostToTimeline({ commit }, { newPostId }) {
      axios.get(route('posts.show', { id: newPostId }))
        .then((response) => {
          commit('UPDATE_UNSHIFTED_TIMELINE_POST', response.data)
          commit('UPDATE_LOADING', false)
        })
    },

    getMe({ commit }) {
      axios.get(route('users.me')).then((response) => {
        commit('UPDATE_SESSION_USER', response.data)
        commit('UPDATE_TIMELINE', response.data)
        commit('UPDATE_UI_FLAGS', response.data)
        commit('UPDATE_LOADING', false)
      })
    },
  },

  getters: {
    is_loading:              state => state.is_loading, // indicates if Vuex has loaded data or not
    vault:                   state => state.vault,
    vaultfolder:             state => state.vaultfolder,
    breadcrumb:              state => state.breadcrumb,
    shares:                  state => state.shares,
    shareables:              state => state.shareables,
    saves:                   state => state.saves,
    feeddata:                state => state.feeddata,
    stories:                 state => state.stories,
    timeline:                state => state.timeline,
    unshifted_timeline_post: state => state.unshifted_timeline_post,
    session_user:            state => state.session_user,
    uiFlags:                 state => state.uiFlags,
    //children: state => state.vault.children, // Flat list
    //mediafiles: state => state.vault.mediafiles, // Flat list
  },
})

/*
  // Find a specific movie by slug
        selected: (state) => (slug) => {
            return state.children.find(o => o.slug === slug);
        },
        */

/*
  // List grouped by genre...
        genres: state => {
            var result = {};
            state.children.forEach( v => {
                v.genres.forEach( g => {
                    if ( !result.hasOwnProperty(g) ) {
                        result[g] = []; // init array for key g, for this genre (array holds movies in this genre)
                    }
                    result[g].push(v);
                });
            });
            return result;
        },
        */
