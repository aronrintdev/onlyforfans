<template>
  <div class="d-flex flex-column">
    <Navigation />
    <SearchAndFilter
      :filters="selectFilters"
      :search="searchQuery"
      :selectFilter="selectedFilter"
      :sortBy="sortBy"
      @selected="filterSelected"
      @searchInput="value => searchQuery = value"
      @filterInput="value => selectedFilter = value"
      @sortByInput="value => sortBy = value"
    />
    <MarkAllRead />
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

import MarkAllRead from './MarkAllRead'
import Navigation from './Navigation'
import SearchAndFilter from './SearchAndFilter'
import ThreadList from './ThreadList'

export default {
  name: 'Sidebar',

  components: {
    MarkAllRead,
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
          key: 'unread',
          label: this.$t('filters.labels.unread'),
          callback: () => this.toggleFilter('is_unread'),
        }, {
          key: 'subscribers',
          label: this.$t('filters.labels.subscribers'),
          callback: () => this.toggleFilter('is_subscriber'),
        }
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
  }),

  methods: {
    ...Vuex.mapActions('messaging', [ 'getThreads', 'searchThreads' ]),

    clearFilters() {
      this.filters = {}
      this.reloadFromFirstPage()
    },

    toggleFilter(key) {
      if ( Object.keys(this.filters).includes(key) ) {
        delete this.filters[key]
      } else {
        this.filters[key] = 1
      }
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
        "subscribers": "Subscribers",
        "unread": "Unread"
      },
    },
    "error": "There was an issue loading your chat threads. Please try again later.",
    "searchError": "There was an issue searching for {{query}}. Please try again later."
  }
}
</i18n>
