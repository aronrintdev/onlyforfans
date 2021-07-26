<template>
  <div class="search-filter">
    <div class="search d-flex align-items-center mb-2 w-100">
      <label for="create-thread-search" v-text="$t('search.label')" class="text-nowrap mr-2 mb-0" />
      <b-input
        ref="searchInput"
        id="create-thread-search"
        :value="search"
        class="flex-grow-1"
        @input="value => $emit('searchInput', value)"
      />
      <b-btn variant="link" @click="onSearchIconClick">
        <fa-icon :icon="search === '' ? 'search' : 'times'" size="lg" />
      </b-btn>
    </div>
    <article class="chatthread-filters py-3 d-flex align-items-center">
      <!-- Filters/View -->
      <span class="mr-2" v-text="$t('filters.label')" />
      <b-form-select :value="selectedFilter" :options="filters" @input="value => $emit('filterInput', value)" />

      <SortControl :value="sortBy" @input="value => $emit('sortByInput', value)" />
    </article>
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

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "filters": {
      "label": "View:",
    },
    "search": {
      "label": "Search",
    }
  }
}
</i18n>
