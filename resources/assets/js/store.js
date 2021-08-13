import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import propSelect from '@helpers/propSelect'

Vue.use(Vuex)

// Routes from window, want to eventually deprecate and move to dedicated compiled routes
const route = window.route

// Modules
import searchModule from './store/search'
import paymentModule from './store/payments'
import bankingModule from './store/banking'
import postsModule from './store/posts'
import statementsModule from './store/statements'
import subscriptionsModule from './store/subscriptions'
import messagingModule from './store/messaging'
import vaultModule from './store/vault'

export default new Vuex.Store({
  modules: {
    search: searchModule,
    payments: paymentModule,
    banking: bankingModule,
    posts: postsModule,
    statements: statementsModule,
    subscriptions: subscriptionsModule,
    messaging: messagingModule,
    vault: vaultModule,
  },

  state: {
    iconStyle: 'fas',
    mobile: false,
    screenSize: 'xs',
    mobileMenuOpen: false,
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
    favorites: null,
    timeline: null,
    session_user: null,
    user_settings: null,
    login_sessions: [],
    uiFlags: [],
    unshifted_timeline_post: null,
    unread_messages_count: 0,
    queue_metadata: {},
  },

  mutations: {
    UPDATE_ICON_STYLE(state, payload) {
      state.iconStyle = payload
    },

    UPDATE_MOBILE(state, payload) {
      state.mobile = payload
    },

    UPDATE_SCREEN_SIZE(state, payload) {
      state.screenSize = payload
    },

    UPDATE_MOBILE_MENU_OPEN(state, payload) {
      state.mobileMenuOpen = payload
    },

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
    UPDATE_FEEDDATA_POST(state, payload) {
      const post = propSelect(payload, 'post')
      const feeddataPosts = state.feeddata.data
      const idx = feeddataPosts.findIndex(p => p.id === post.id)
      feeddataPosts[idx] = post
      state.feeddata = {
        ...state.feeddata,
        data: [...feeddataPosts]
      }
    },
    UPDATE_QUEUE_METADATA(state, payload) {
      state.queue_metadata = payload.hasOwnProperty('data') ? payload.data.meta : {}
    },
    UPDATE_PREVIEWPOSTS(state, payload) {
      state.previewposts = payload.hasOwnProperty('data') ? payload.data : {}
    },
    UPDATE_STORIES(state, payload) {
      state.stories = propSelect(payload, 'stories')
    },
    UPDATE_FAVORITES(state, payload) {
      state.favorites = payload.hasOwnProperty('data') ? payload.data : {}
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
    UPDATE_UNREAD_MESSAGES_COUNT(state, payload) {
      state.unread_messages_count = propSelect(payload, 'total_unread_count')
    },
  },

  actions: {
    getVault({ commit }, id) {
      axios.get(route('vaults.show', { id }))
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
      feedType='default', 
      timelineId, 
      isHomefeed,
      page=1, 
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
      let url
      switch (feedType) {
        case 'photos':
          url = route('timelines.photos', timelineId)
          break
        case 'videos':
          url = route('timelines.videos', timelineId)
          break
        case 'schedule':
          url = `/timelines/home/scheduled-feed`
          break
        default:
          url = isHomefeed ? `/timelines/home/feed` : `/timelines/${timelineId}/feed`
      }
      axios.get(url, { params }).then( (response) => {
        if (feedType === 'schedule') {
          commit('UPDATE_QUEUE_METADATA', response);
        }
        commit('UPDATE_FEEDDATA', response);
      })
    },

    getPreviewposts( { commit }, { timelineId, limit } ) {
      const params = {
        limit
      }
      //const url = `/timeline/${timelineId}/preview-posts/?take=${limit}`
      axios.get( route('timelines.previewPosts', timelineId), { params } ).then( response => {
        commit('UPDATE_PREVIEWPOSTS', response.data);
      });
    },

    getFavorites({ commit }, params ) {
      const url = route(`favorites.index`);
      axios.get(url, { params })
        .then((response) => {
          commit('UPDATE_FAVORITES', response)
        })
    },

    getStories({ commit }, payload ) {
      //const username = this.state.session_user.username // Not used - This param will eventually be DEPRECATED
      // %FIXME
      const params = {}
      if (Object.keys(payload).includes('timeline_id')) {
        params.timeline_id = payload.timeline_id
      } else if (Object.keys(payload).includes('following')) {
        params.following = 1
      } else {
        params.following = 1 // default
      }
      if (Object.keys(payload).includes('stypes')) {
        params.stypes = payload.stypes
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

    getUnreadMessagesCount({ commit }) {
      axios.get(route('chatthreads.totalUnreadCount')).then((response) => {
        commit('UPDATE_UNREAD_MESSAGES_COUNT', response.data)
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
    
    getQueueMetadata( { commit } ) {
      const params = {
        page: 1,
        take: 5,
        sortBy: 'latest',
        hideLocked: false,
        hidePromotions: false,
      }
      axios.get(`/timelines/home/scheduled-feed`, { params }).then( (response) => {
        commit('UPDATE_QUEUE_METADATA', response);
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
    favorites:               state => state.favorites,
    timeline:                state => state.timeline,
    unshifted_timeline_post: state => state.unshifted_timeline_post,
    session_user:            state => state.session_user,
    user_settings:           state => state.user_settings,
    login_sessions:          state => state.login_sessions,
    uiFlags:                 state => state.uiFlags,
    unread_messages_count:   state => state.unread_messages_count,
    queue_metadata:          state => state.queue_metadata,
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
