/**
 * Vuex Search module definitions
 */
import _ from 'lodash'

const route = window.route

export const search = {
  namespaced: true,

  state: () => ({
    /** Search query user has typed */
    query: '',

    /** Current saved result set */
    results: {
      timelines: [],
      stories: [],
      posts: [],
    },

    /** List of autoCompleteSuggestions */
    autoCompleteSuggestions: [],

    /** Definitions of different types of search results */
    groupDefinitions: [
      {
        name: 'timelines',
        label: 'Users',
        icon: 'users',
        component: 'TimelineDisplay',
      },
      {
        name: 'stories',
        label: 'Stories',
        icon: 'pen',
        component: 'StoryDisplay',
      },
      {
        name: 'posts',
        label: 'Posts',
        icon: 'stream',
        component: 'PostDisplay',
      },
    ],
  }),

  getters: {
    availableGroups(state) {
      return _.filter(state.groupDefinitions, group => state.results[group.name].length > 0)
    },
    timelines(state) {
      return state.results.timelines || []
    },
    stories(state) {
      return state.results.stories || []
    },
    posts(state) {
      return state.results.posts || []
    },
  },

  mutations: {
    UPDATE_QUERY(state, payload) {
      state.query = payload
    },

    UPDATE_RESULTS(state, payload) {
      state.autoCompleteSuggestions = !payload.hasOwnProperty('autoComplete')
        ? []
        : payload.autoComplete.hasOwnProperty('data')
          ? payload.autoComplete.data
          : payload.autoComplete
      state.groupDefinitions.forEach(group => {
        state.results[group.name] = !payload.hasOwnProperty(group.name)
          ? []
          : payload[group.name].hasOwnProperty('data')
            ? payload[group.name].data
            : payload[group.name]
      })
    }
  },

  actions: {
    search({ commit, state }, take = 10) {
      return new Promise((resolve, reject) => {
        if (state.query) {
          axios.get(route('search', { q: state.query, take }))
            .then(response => {
              commit('UPDATE_RESULTS', response.data)
              resolve()
            })
            .catch(error => reject(error))
        } else {
          resolve()
        }
      })
    },
  },
}

export default search
