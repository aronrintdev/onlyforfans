<template>
  <div class="search-filter py-3">
    <label for="thread-search" v-text="$t('search.label')" class="text-nowrap mb-0" />
    <b-input
      ref="searchInput"
      id="thread-search"
      :value="search"
      class="flex-grow-1"
      @input="value => $emit('searchInput', value)"
    />
    <b-btn variant="link" @click="onSearchIconClick">
      <fa-icon :icon="search === '' ? 'search' : 'times'" size="lg" />
    </b-btn>

    <!-- Filters/View -->
    <label for="thread-filter-select" class="mb-0" v-text="$t('filters.label')" />
    <b-form-select
      id="thread-filter-select"
      :value="selectedFilter"
      :options="filters"
      @input="value => $emit('filterInput', value)"
    />
    <SortControl :value="sortBy" :asc="asc" @input="value => $emit('sortByInput', value)" @sortBy="value => $emit('setAscending', value)" />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/SearchAndFilter.vue
 */
import Vuex from 'vuex'
import SortControl from './SortControl'

export default {
  name: 'SearchAndFilter',

  components: {
    SortControl,
  },

  props: {
    search: { type: String, default: '' },
    selectedFilter: { type: String, default: 'all' },
    filters: { type: Array, default: () => ([]) },
    sortBy: { type: String, default: 'recent' },
    asc: { type: Boolean, default: false },
  },

  computed: {},

  data: () => ({}),

  methods: {
    onSearchIconClick() {
      if (this.search === '') {
        // Focus the search box
        this.$refs.searchInput.$el.focus()
      } else {
        // Clear search term
        this.$emit('searchInput', '')
      }
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.search-filter {
  display: grid;
  grid-gap: 0.5rem;
  grid-template-columns: auto auto auto;
  align-items: center;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "filters": {
      "label": "View:",
    },
    "search": {
      "label": "Search:",
    }
  }
}
</i18n>
