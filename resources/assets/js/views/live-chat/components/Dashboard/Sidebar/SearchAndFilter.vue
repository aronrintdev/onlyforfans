<template>
  <div class="search-filter py-3">
    <div :class="searchClass">
      <label for="thread-search" v-text="$t('search.label')" class="text-nowrap mr-3 mb-0" />
      <b-input
        ref="searchInput"
        id="thread-search"
        :value="search"
        class="flex-grow-1"
        @input="value => $emit('searchInput', value)"
      />
    </div>
    <div class="filter">
      <!-- Filters/View -->
      <FilterControl :value="selectFilter" :filters="filters" @input="value => $emit('filterInput', value)" />
      <SortControl :value="sortBy" :asc="asc" @input="value => $emit('sortByInput', value)" @sortBy="value => $emit('setAscending', value)" />
      <MarkControl @updateThreadsAllRead="$emit('updateThreadsAllRead')" />
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/SearchAndFilter.vue
 */
import Vuex from 'vuex'
import FilterControl from './FilterControl.vue'
import MarkControl from './MarkControl.vue'
import SortControl from './SortControl'

export default {
  name: 'SearchAndFilter',

  components: {
    SortControl,
    FilterControl,
    MarkControl,
  },

  props: {
    enableSearch: { type: Boolean, default: false },
    search: { type: String, default: '' },
    selectFilter: { type: String, default: 'all' },
    filters: { type: Array, default: () => ([]) },
    sortBy: { type: String, default: 'recent' },
    asc: { type: Boolean, default: false },
  },

  computed: {},

  data: () => ({
    searchClass: 'search',
  }),

  methods: {},

  watch: {
    enableSearch(value) {
      if (value) {
        this.$refs.searchInput.$el.focus()
        this.searchClass = 'search active mb-3'
      } else {
        this.searchClass = 'search'
      }
    }
  },

  created() {},
}
</script>

<style lang="scss" scoped>
.search-filter {
  display: flex;
  flex-direction: column;
}
.search {
  display: flex;
  align-items: center;
  opacity: 0;
  height: 0;
  &.active {
    height: unset;
    opacity: 1;
  }
}
.filter {
  display: flex;
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
