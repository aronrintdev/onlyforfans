<template>
  <div class="d-flex flex-column h-100 pt-3 pb-0">
    <Navigation
      :enableSearch="enableSearch"
      @onSearchIconClick="onSearchIconClick"
    />
    <SearchAndFilter
      :enableSearch="enableSearch"
      :filters="selectFilters"
      :search="searchQuery"
      :selectFilter="selectedFilter"
      :sortBy="sortBy"
      :asc="asc"
      @selected="filterSelected"
      @searchInput="value => searchQuery = value"
      @filterInput="value => selectedFilter = value"
      @sortByInput="value => sortBy = value"
      @setAscending="value => asc = value"
      @updateThreadsAllRead="getChatthreads"
      ref="childComponentRef"
    />
    <ThreadList
      :threads="renderedThreads"
      :loading="loading"
      :page="page"
      :perPage="take"
      :totalRows="totalRows"
      :showSearchResults="showSearchResults"
      :searchResults="searchResults"
      :searchQuery="searchQuery"
      @pageChange="pageChange"
    />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar.vue
 */
import Vuex from 'vuex'

import Navigation from './Navigation'
import SearchAndFilter from './SearchAndFilter'
import ThreadList from './ThreadList'

export default {
  name: 'Sidebar',

  components: {
    Navigation,
    SearchAndFilter,
    ThreadList,
  },

  props: {
    state: { type: String, default: 'loading' },
  },

  computed: {
    ...Vuex.mapState('messaging', [ 'threads', 'threadMeta' ]),

    activeThreadId() {
      return this.$route.params.id
    },
    activeThread() {
      return this.chatthreads.find( ct => ct.id === this.activeThreadId )
    },

    availableFilters() {
      return [
        {
          key: 'all',
          label: this.$t('filters.labels.all'),
          callback: () => {
            this.clearFilters()
          }
        }, {
          key: 'favorites',
          label: this.$t('filters.labels.favorites'),
          callback: () => this.toggleFilter('is_favorite'),
        }, {
          key: 'freeFollowers',
          label: this.$t('filters.labels.freeFollowers'),
          callback: () => this.toggleFilter('is_free_follower'),
        }, {
          key: 'subscribers',
          label: this.$t('filters.labels.subscribers'),
          callback: () => this.toggleFilter('is_subscriber'),
        }, {
          key: 'following',
          label: this.$t('filters.labels.following'),
          callback: () => this.toggleFilter('is_following'),
        }, 
      ]
    },

    linkCreateThread() {
      return { name: 'chatthreads.create' }
    },

    renderedThreads() {
      if (this.showSearchResults) {
        return this.searchResults
      }
      return this.renderItems.map(id => (this.threads[id]))
    },

    selectFilters() {
      return this.availableFilters.map(o => ({ value: o.key, text: o.label }))
    },

    totalRows() {
      return this.threadMeta.total || 0
    },
  },

  data: () => ({
    loading: false,
    searchQuery: '',
    showSearchResults: false,
    searchResults: [],

    renderItems: [],

    page: 1,
    take: 10,
    selectedFilter: 'all',
    filters: {},
    sortBy: 'recent',
    asc: false,
    enableSearch: false,
  }),

  methods: {
    ...Vuex.mapActions('messaging', [ 'getThreads', 'searchThreads' ]),

    clearFilters() {
      this.filters = {}
      this.reloadFromFirstPage()
    },

    toggleFilter(key) {
      this.filters = {}
      this.filters[key] = 1
      this.reloadFromFirstPage()
    },

    filterSelected(key) {
      this.selectedFilter = key
    },

    getChatthreads() {
      if (this.loading) {
        return
      }
      this.loading = true
      this.getThreads({
        page: this.page,
        take: this.take,
        filters: this.filters,
        sortBy: this.sortBy,
        asc: this.asc,
      }).then(response => {
        this.renderItems = response.data.data.map(o => (o.id))
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('error')})
      }).finally(() => {
        this.loading = false
      })
    },

    pageChange(page) {
      this.page = page
    },

    reloadFromFirstPage() {
      this.page = 1
      this.getChatthreads()
    },

    _doSearch(q) {
      this.searchThreads({ q })
        .then(response => {
          this.searchResults = response.data.data
          this.showSearchResults = true
        })
        .catch(error => eventBus.$emit('error', { error, message: this.$t('searchError', { query: q }) }))
    },

    doSomething() {},

    async getQueue() {
      const response = await axios.get(route('chatmessagegroups.queue'))
    },

    onSearchIconClick() {
      this.enableSearch = !this.enableSearch
      if (this.enableSearch) {
        this.searchQuery = ''
      }
    },
  },

  watch: {
    page() {
      this.getChatthreads()
    },
    searchQuery(value) {
      // If cleared then unset search results
      if (value === '') {
        this.showSearchResults = false
        this.searchResults = []
      } else {
        // Debounced search
        this.doSearch(value)
      }
    },
    selectedFilter(value) {
      const index = _.findIndex(this.availableFilters, o => o.key === value)
      if (index > -1 && typeof this.availableFilters[index].callback === 'function') {
        this.availableFilters[index].callback()
      }
    },
    sortBy() {
      this.reloadFromFirstPage()
    },
    asc() {
      this.reloadFromFirstPage()
    },
    state(value) {
      if (value === 'loaded') {
        this.reloadFromFirstPage()
      }
    }
  },

  created() {
    if (this.state === 'loaded') {
      this.reloadFromFirstPage()
    }
    this.doSearch = _.debounce(this._doSearch, this.searchDebounceDuration);

    this.getQueue()
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "filters": {
      "labels": {
        "all": "All",
        "favorites": "Favorites",
        "freeFollowers": "Free Followers",
        "subscribers": "Paid Subscribers",
        "following": "Who I Follow",
        "unread": "Unread"
      },
    },
    "error": "There was an issue loading your chat threads. Please try again later.",
    "searchError": "There was an issue searching for {{query}}. Please try again later."
  }
}
</i18n>
