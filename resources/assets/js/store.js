import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

/*
https://wookie.codesubmit.io/movies
https://wookie.codesubmit.io/movies?q=<search_term>
  ~ For authentication pass the "Authorization: Bearer Wookie2019" header
  */

Vue.use(Vuex);

//axios.defaults.baseURL = '/';
//axios.defaults.headers.common['Authorization'] = 'Bearer Wookie2019';
//axios.defaults.headers.get['Accepts'] = 'application/json';

export default new Vuex.Store({

    state: {
        children: [],
        is_loading: true
    },

    mutations: {
        UPDATE_CHILDREN (state, payload) {
            state.children = payload.hasOwnProperty('children') ? payload.children : [];
        },
        UPDATE_LOADING(state, payload) {
            state.is_loading = payload;
        },
    },

    actions: {
        getChildren({ commit }, vfId='root') {
            const url = `/vaultfolders?vf_id=${vfId}`;
            axios.get(url).then( (response) => {
                commit('UPDATE_CHILDREN', response.data);
                commit('UPDATE_LOADING', false);
            });
        },
      /*
        getSearch({ commit }, querystr) {
            axios.get('/movies?q='+querystr).then( (response) => {
                commit('UPDATE_CHILDREN', response.data);
                commit('UPDATE_LOADING', false);
            });
        },
        */
    },

    getters: {
        is_loading: state => state.is_loading, // indicates if Vuex has loaded data or not

        children: state => state.children, // Flat list

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

    }
});

