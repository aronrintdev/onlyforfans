import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

Vue.use(Vuex);

export default new Vuex.Store({

  state: {
    vault: {},
    vaultfolder: {},
    breadcrumb: [],
    shares: [], // shares for a vaultfolder, used to mark what resources session user *has* shared out
    shareables: [], // resources that have been shared with session user
    saves: [], // resources that session user has saved
    feeditems: [],
    timeline: null,
    me: null,
    unshifted_timeline_post: null,
    is_loading: true,
  },

  mutations: {
    UPDATE_VAULT (state, payload) {
      state.vault = payload.hasOwnProperty('vault') ? payload.vault : [];
    },
    UPDATE_VAULTFOLDER (state, payload) {
      state.vaultfolder = payload.hasOwnProperty('vaultfolder') ? payload.vaultfolder : [];
    },
    UPDATE_BREADCRUMB (state, payload) {
      state.breadcrumb = payload.hasOwnProperty('breadcrumb') ? payload.breadcrumb : [];
    },
    UPDATE_SHARES (state, payload) { 
      state.shares = payload.hasOwnProperty('shares') ? payload.shares : [];
    },
    UPDATE_SHAREABLES (state, payload) {
      state.shareables = payload.hasOwnProperty('shareables') ? payload.shareables : [];
    },
    UPDATE_SAVES (state, payload) {
      state.saves = payload.hasOwnProperty('saves') ? payload.saves : [];
    },
    UPDATE_FEEDITEMS (state, payload) {
      //console.log('mutation', payload);
      state.feeditems = payload.hasOwnProperty('feeditems') ? payload.feeditems : [];
    },
    UPDATE_TIMELINE (state, payload) {
      state.timeline = payload.hasOwnProperty('timeline') ? payload.timeline : [];
    },
    UPDATE_SESSION_USER (state, payload) {
      state.session_user = payload.hasOwnProperty('session_user') ? payload.session_user : [];
    },
    UPDATE_UNSHIFTED_TIMELINE_POST (state, payload) {
      state.unshifted_timeline_post = payload.hasOwnProperty('post') ? payload.post : [];
    },
    UPDATE_LOADING(state, payload) {
      state.is_loading = payload;
    },
  },

  actions: {
    getVault({ commit }, pkid) {
      const url = `/vaults/${pkid}`;
      axios.get(url).then( (response) => {
        commit('UPDATE_VAULT', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    getVaultfolder({ commit }, pkid) {
      const url = `/vaultfolders/${pkid}`;
      axios.get(url).then( (response) => {
        commit('UPDATE_VAULTFOLDER', response.data);
        commit('UPDATE_BREADCRUMB', response.data);
        commit('UPDATE_SHARES', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    getShareables({ commit }) {
      const url = `/shareables`;
      axios.get(url).then( (response) => {
        commit('UPDATE_SHAREABLES', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    getSaves({ commit }) {
      const url = `/saved`;
      axios.get(url).then( (response) => {
        commit('UPDATE_SAVES', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    getFeeditems( { commit }, { timelineSlug, page, limit } ) {
      console.log(`Loading page ${page}`);
      const url = `/timelines/${timelineSlug}/feeditems?page=${page}&take=${limit}`;
      axios.get(url).then( (response) => {
        commit('UPDATE_FEEDITEMS', response.data);
        commit('UPDATE_TIMELINE', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    unshiftPostToTimeline( { commit }, { newPostId } ) {
      const url = `/posts/${newPostId}`;
      axios.get(url).then( (response) => {
        commit('UPDATE_UNSHIFTED_TIMELINE_POST', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
    getMe( { commit } ) {
      const url = `/users/me`;
      axios.get(url).then( (response) => {
        commit('UPDATE_SESSION_USER', response.data);
        commit('UPDATE_TIMELINE', response.data);
        commit('UPDATE_LOADING', false);
      });
    },
  },

  getters: {
    is_loading: state => state.is_loading, // indicates if Vuex has loaded data or not
    vault: state => state.vault,
    vaultfolder: state => state.vaultfolder,
    breadcrumb: state => state.breadcrumb,
    shares: state => state.shares,
    shareables: state => state.shareables,
    saves: state => state.saves,
    feeditems: state => state.feeditems,
    timeline: state => state.timeline,
    unshifted_timeline_post: state => state.unshifted_timeline_post,
    session_user: state => state.session_user,
    //children: state => state.vault.children, // Flat list
    //mediafiles: state => state.vault.mediafiles, // Flat list
  },
});

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

