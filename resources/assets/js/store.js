import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

// Routes from window, want to eventually deprecate and move to dedicated compiled routes
const route = window.route

// Helps select items from response result set
// @param {Object} payload - response payload
// @param {String} propertyName - name of property
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
    shares: [], // shares for a vaultfolder, used to mark resources session user *has* shared out
    shareables: [], // resources shared with session user
    saves: [], // resources session user has saved
    feeditems: [], // Posts on current open timeline
    feeddata: {},
    previewposts: null,
    stories: [], // Current open stories
    earnings: null,
    debits: null,
    fanledgers: {},
    ledgercredits: null,
    ledgerdebits: null,
    bookmarks: null,
    timeline: null,
    session_user: null, 
    user_settings: null,
    login_sessions: [],
    uiFlags: [],
    unshifted_timeline_post: null,
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
      state.feeddata = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_PREVIEWPOSTS(state, payload) {
      state.previewposts = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_STORIES(state, payload) {
      state.stories = propSelect(payload, 'stories')
    },
    UPDATE_FANLEDGERS(state, payload) {
      //state.fanledgers = propSelect(payload, 'fanledgers')
      state.fanledgers = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_LEDGERCREDITS(state, payload) {
      state.ledgercredits = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_LEDGERDEBITS(state, payload) {
      state.ledgerdebits = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_EARNINGS(state, payload) {
      state.earnings = propSelect(payload, 'earnings')
    },
    UPDATE_DEBITS(state, payload) {
      state.debits = propSelect(payload, 'debits')
    },
    UPDATE_BOOKMARKS(state, payload) {
      state.bookmarks = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_TIMELINE(state, payload) {
      state.timeline = propSelect(payload, 'timeline')
    },
    UPDATE_SESSION_USER(state, payload) {
      state.session_user = propSelect(payload, 'session_user')
    },
    UPDATE_USER_SETTINGS(state, payload) {
      state.user_settings = propSelect(payload, 'user_settings')
    },
    UPDATE_LOGIN_SESSIONS(state, payload) {
      state.login_sessions = propSelect(payload, 'login_sessions')
    },
    UPDATE_UI_FLAGS(state, payload) {
      state.uiFlags = { ...state.uiFlags, ...propSelect(payload, 'uiFlags', 'object') }
    },
    UPDATE_UNSHIFTED_TIMELINE_POST(state, payload) {
      state.unshifted_timeline_post = propSelect(payload, 'post')
    },
  },

  actions: {
    getVault({ commit }, id) {
      axios.get(route('valuts.show', { id }))
        .then((response) => {
          commit('UPDATE_VAULT', response.data)
        })
    },

    getVaultfolder({ commit }, id) {
      axios.get(route('vaultfolders.show', { id }))
        .then((response) => {
          commit('UPDATE_VAULTFOLDER', response.data)
          commit('UPDATE_BREADCRUMB', response.data)
          commit('UPDATE_SHARES', response.data)
        })
    },

    getShareables({ commit }) {
      axios.get(route('shareables.index'))
        .then((response) => {
          commit('UPDATE_SHAREABLES', response.data)
        })
    },

    getSaves({ commit }) {
      // Route does not exist
      const url = `/saved`
      axios.get(url).then((response) => {
        commit('UPDATE_SAVES', response.data)
      })
    },

    getFeeddata( { commit }, { 
      timelineId, 
      isHomefeed,
      page, 
      limit, 
      sortBy='latest', 
      hideLocked=false,
      hidePromotions=false,
    }) {
      const params = {
        page,
        take: limit,
        sortBy,
        hideLocked,
        hidePromotions,
      }
      const url = isHomefeed ? `/timelines/home/feed` : `/timelines/${timelineId}/feed`
      axios.get(url, { params }).then( (response) => {
        commit('UPDATE_FEEDDATA', response);
      })
    },

    getPreviewposts( { commit }, { timelineId, limit } ) {
      //const url = `/timeline/${timelineId}/preview-posts/?take=${limit}`
      axios.get( route('timelines.previewPosts', timelineId) ).then( response => {
        commit('UPDATE_PREVIEWPOSTS', response.data);
      });
    },

    getFanledgers({ commit }, params ) {
      const url = route(`fanledgers.index`);
      axios.get(url, { params })
        .then((response) => {
          commit('UPDATE_FANLEDGERS', response)
        })
    },
    getLedgercredits({ commit }, params ) {
      const url = route(`fanledgers.index`);
      axios.get(url, { params })
        .then((response) => {
          commit('UPDATE_LEDGERCREDITS', response)
        })
    },
    getLedgerdebits({ commit }, params ) {
      const url = route(`fanledgers.index`);
      axios.get(url, { params })
        .then((response) => {
          commit('UPDATE_LEDGERDEBITS', response)
        })
    },

    getEarnings({ commit }, { user_id }  ) {
      axios.get(route('fanledgers.showEarnings', user_id)).then((response) => {
        commit('UPDATE_EARNINGS', response.data)
      })
    },
    getDebits({ commit }, { user_id }  ) {
      axios.get(route('fanledgers.showDebits', user_id)).then((response) => {
        commit('UPDATE_DEBITS', response.data)
      })
    },

    getBookmarks({ commit }, params ) {
      const url = route(`bookmarks.index`);
      axios.get(url, { params })
        .then((response) => {
          commit('UPDATE_BOOKMARKS', response)
        })
    },

    getStories({ commit }, { filters }) {
      //const username = this.state.session_user.username // Not used - This param will eventually be DEPRECATED
      // %FIXME
      const params = {}
      if (Object.keys(filters).includes('timeline_id')) {
        params.user_id = filters.timeline_id
      } else if (Object.keys(filters).includes('following')) {
        params.following = true
      } else {
        params.following = true // default
      }
      axios.get(route('stories.index'), { params })
        .then((response) => {
          commit('UPDATE_STORIES', response.data)
        })
    },

    unshiftPostToTimeline({ commit }, { newPostId }) {
      axios.get(route('posts.show', { id: newPostId }))
        .then((response) => {
          commit('UPDATE_UNSHIFTED_TIMELINE_POST', response.data)
        })
    },

    getMe({ commit }) {
      axios.get(route('users.me')).then((response) => {
        commit('UPDATE_SESSION_USER', response.data)
        commit('UPDATE_TIMELINE', response.data)
        commit('UPDATE_UI_FLAGS', response.data)
      })
    },

    getUserSettings({ commit }, { userId }) {
      axios.get(route('users.showSettings', { id: userId })).then((response) => {
        commit('UPDATE_USER_SETTINGS', response.data)
      })
    },

    getLoginSessions({ commit }, { params }) {
      axios.get(route('sessions.index', { params })).then((response) => {
        commit('UPDATE_LOGIN_SESSIONS', response)
      })
    },
  },

  getters: {
    vault:                   state => state.vault,
    vaultfolder:             state => state.vaultfolder,
    breadcrumb:              state => state.breadcrumb,
    shares:                  state => state.shares,
    shareables:              state => state.shareables,
    saves:                   state => state.saves,
    feeddata:                state => state.feeddata,
    previewposts:            state => state.previewposts,
    stories:                 state => state.stories,
    fanledgers:              state => state.fanledgers,
    ledgercredits:           state => state.ledgercredits,
    ledgerdebits:            state => state.ledgerdebits,
    earnings:                state => state.earnings,
    debits:                  state => state.debits,
    bookmarks:               state => state.bookmarks,
    timeline:                state => state.timeline,
    unshifted_timeline_post: state => state.unshifted_timeline_post,
    session_user:            state => state.session_user,
    user_settings:           state => state.user_settings,
    login_sessions:          state => state.login_sessions,
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
