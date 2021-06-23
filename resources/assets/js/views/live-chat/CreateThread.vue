<template>
  <div v-if="!isLoading" class="container-xl px-3 py-3" id="view-create-thread">

    <section class="row h-100">

      <aside class="col-md-5 col-lg-4">

        <article class="top-bar d-flex justify-content-between align-items-center">
          <div class="h4" v-text="$t('title')" />
          <b-btn variant="link" :to="{ name: 'chatthreads.dashboard' }">
            <fa-icon icon="arrow-left" fixed-width size="lg" />
            <span v-text="$t('nav.return')" />
          </b-btn>
        </article>

        <article class="mycontacts-sort pt-3 d-flex flex-column justify-content-between align-items-center">
          <Search v-model="searchQuery" :label="$t('search.label')" />

          <div class="d-flex pt-3 pb-2 w-100">
            <!-- Quick Filters Buttons -->
            <article class="d-flex flex-wrap align-items-center flex-grow-1">
              <FilterSelect
                v-for="filter in quickAccessFiltersList"
                :key="filter.key"
                :label="filtersLabel(filter.key)"
                :selected="selectedFilter === filter.key"
                selectedVariant="secondary"
                variant="light"
                @selected=" selectedFilter = filter.key"
                class="mx-1 my-1"
              />
              <FilterSelect
                v-if="showAddFilter"
                no-selected-icon
                variant="light"
                selectedVariant="secondary"
                @selected="filterAdd"
              >
                <fa-icon :icon="['fas', 'plus']" />
              </FilterSelect>
            </article>

            <!-- Filters Dropdown -->
            <b-dropdown
              ref="filterControls"
              class="filter-controls"
              variant="link"
              size="sm"
              right
              no-caret
            >
              <template #button-content>
                <b-badge show class="alert-primary" :style="{ fontSize: '100%' }">
                  <span class="mr-2" v-text="$t('filter.label')" /> <fa-icon icon="filter" />
                </b-badge>
              </template>

              <b-dropdown-header>
                Apply Filter
              </b-dropdown-header>

              <b-dropdown-item
                v-for="filter in filters"
                :key="filter.key"
                :active="filter.key === selectedFilter"
                @click="selectedFilter = filter.key"
              >
                <fa-icon
                  :style="{ opacity: inQuickAccessFilters(filter) ? '100%': '0' }"
                  icon="thumbtack"
                  class="mx-2"
                  size="lg"
                  fixed-width
                />
                {{ filtersLabel(filter.key) }}
              </b-dropdown-item>
              <b-dropdown-divider v-if="showAddFilter" />
              <b-dropdown-item v-if="showAddFilter">
                <fa-icon icon="plus" class="text-success mx-2" size="lg" fixed-width />
                {{ $t('filters.add') }}
              </b-dropdown-item>
            </b-dropdown>

            <!-- Sort Control Dropdown -->
            <b-dropdown ref="sortCtrls" class="filter-controls" variant="link" size="sm" right no-caret>
              <template #button-content>
                <fa-icon :icon="['fas', 'sort-amount-down']" class="fa-lg" />
              </template>

              <b-dropdown-header>
                Sort By
              </b-dropdown-header>

              <b-dropdown-item :active="sortBy === 'recent'" @click="sortBy = 'recent'" >
                <fa-icon icon="sort-up" fixed-width class="mx-2" size="lg" />
                Recently Added
              </b-dropdown-item>
              <b-dropdown-item :active="sortBy === 'oldest'" @click="sortBy = 'oldest'" >
                <fa-icon icon="sort-down" fixed-width class="mx-2" size="lg" />
                Oldest Added
              </b-dropdown-item>
            </b-dropdown>
          </div>

          <!-- Extra Filters Collapse -->
          <b-collapse :visible="!!extraFilters" class="w-100">
            <div class="my-2 w-100 d-flex flex-column">
              <div v-for="item in extraFilters" :key="item" class="w-100 d-flex">
                <!-- TODO: hookup proper components for each extra filter -->
                <div v-if="item === 'totalSpent'" class="w-100 d-flex align-items-center">
                  <b-form-checkbox />
                  <label for="total-spent" class="text-nowrap mb-0 mr-2">Total Spent:</label>
                  <b-input-group prepend="$" size="sm" class="flex-grow-1">
                    <b-form-input id="total-spent" size="sm" />
                  </b-input-group>
                </div>
              </div>
              <b-btn variant="primary" size="sm" class="mt-2 ml-auto">
                <fa-icon icon="filter" class="mr-1" /> Apply
              </b-btn>
            </div>
          </b-collapse>

        </article>

        <article class="contact-list position-relative">
          <!-- Select All -->
          <div class="mb-2 d-flex justify-content-end align-items-center">
            <span
              v-if="selectedContactsCount > 0"
              class="text-muted mr-3 select-none"
              v-text="$t('selectedCount', { count: selectedContactsCount })"
            />

            <label for="select-all" v-text="$t('selectAll')" class="cursor-pointer select-none mr-2 mb-0" />
            <b-form-checkbox
              id="select-all"
              v-model="selectAll"
              :value="true"
              :indeterminate="selectIndeterminate"
              inline
              class="cursor-pointer mr-2 mb-1"
              size="lg"
            />
          </div>

          <b-list-group>
            <PreviewContact
              v-for="contact in renderedItems"
              :key="contact.id"
              :data-ct_id="contact.id"
              class="px-2"
              :contact="contact"
              @input="onContactInput"
            />
            <LoadingOverlay :loading="isLoadingContacts" />
            <LoadingOverlay :loading="isSearching" :text="$t('search.searching')" />
            <b-list-group-item v-if="showSearchResults && renderedItemsCount === 0" class="text-center">
              {{ $t('search.no-results', { search: searchQuery }) }}
            </b-list-group-item>
            <b-list-group-item v-else-if="renderedItemsCount === 0" class="text-center">
              {{ $t('no-results') }}
            </b-list-group-item>
          </b-list-group>
        </article>

      </aside>

      <main class="col-md-7 col-lg-8">
        <CreateThreadForm
          :session_user="session_user"
          v-on:create-chatthread="createChatthread($event)"
          class="h-100"
        />
      </main>

    </section>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/CrateThread.vue
 */
import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'
import { eventBus } from '@/app'

import CreateThreadForm from '@views/live-chat/components/CreateThreadForm'
import FilterSelect from './components/FilterSelect.vue'
import PreviewContact from '@views/live-chat/components/PreviewContact'
import Search from '@views/live-chat/components/Search'

import contains from '@helpers/contains'

import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: 'CreateThread',

  components: {
    CreateThreadForm,
    FilterSelect,
    LoadingOverlay,
    PreviewContact,
    Search,
  },

  /* ------------------------------------------------------------------------ */
  /*                                 COMPUTED                                 */
  /* ------------------------------------------------------------------------ */
  computed: {
    ...Vuex.mapGetters(['session_user']),
    ...Vuex.mapState('messaging/contacts', [
      'contacts', 'cache', 'pinned', 'filters',
    ]),
    ...Vuex.mapGetters('messaging/contacts', [
      'contactsCount',
      'getContactsFor',
      'getAllPagesContacts',
      'pinnedFilters',
      'selectedContacts',
      'selectedContactsCount',
    ]),

    isLoading() {
      return !this.session_user
    },

    quickAccessFiltersList() {
      return _.filter(this.filters, o => (
        o.always || this.inQuickAccessFilters(o) || o.key === this.selectedFilter
      ))
    },

    renderedItemsCount() {
      if (!this.renderedItems) {
        return 0
      }
      return Object.keys(this.renderedItems).length
    },

    extraFilters() {
      return this.filters[this.selectedFilter].extraFilters
    },

    searchResultsCount() {
      return Object.keys(this.searchResults).length
    },

    renderedItems() {
      if (this.showSearchResults) {
        return _.filter(this.contacts, o => contains(this.searchResults, o.id))
      }
      return this.getAllPagesContacts(this.currentPageObject)
    },

    currentPageObject() {
      return {
        filter: this.selectedFilter,
        page: this.currentPage,
        take: this.perPage,
        sort: this.sortBy,
      }
    },

  }, // computed()

  /* ------------------------------------------------------------------------ */
  /*                                   DATA                                   */
  /* ------------------------------------------------------------------------ */
  data: () => ({
    // Content Switches
    showAddFilter: false,

    // List State
    currentPage: 1,
    perPage: 10,
    sortBy: 'recent',
    selectedFilter: 'all',

    // Loading
    isLoadingContacts: false,

    // Selection Flags
    selectAll: false,
    selectIndeterminate: false,

    // Search Items
    searchQuery: '',
    isSearching: false,
    showSearchResults: false,
    searchResults: [],
    searchDebounceDuration: 500,
  }), // data

  created() {
    this.getMe()
    // Create debounced method
    this.doSearch = _.debounce(this._doSearch, this.searchDebounceDuration);
  },

  mounted() { },

  /* ------------------------------------------------------------------------ */
  /*                                  METHODS                                 */
  /* ------------------------------------------------------------------------ */
  methods: {
    ...Vuex.mapActions([
      'getMe',
    ]),
    ...Vuex.mapMutations('messaging/contacts', [
      'UPDATE_CONTACT',
      'SAVE_CONTACTS_LIST',
      'UNSELECT_ALL',
      'SELECT_CONTACTS',
    ]),
    ...Vuex.mapActions('messaging/contacts', [
      'loadContacts',
    ]),

    filterAdd() {
      // TODO: Adding of filters
    },

    filtersLabel(key) {
      if (this.filters[this.selectedFilter].name) {
        return this.filters[this.selectedFilter].name
      }
      return this.$t(`filters.${key}`)
    },

    onContactInput(value) {
      this.UPDATE_CONTACT(value)

      if ( this.selectedContactsCount < this.renderedItemsCount ) {
        this.selectIndeterminate = true
      }
      if ( this.selectedContactsCount === this.renderedItemsCount ) {
        this.selectIndeterminate = false
        this.selectAll = true
      }
      if (this.selectedContactsCount === 0) {
        this.selectIndeterminate = false
        this.selectAll = false
      }
    },

    /**
     * attempts to load set of contacts if not in items set is not in cache
     * @param {Bool} force - Forces the set to load from server, even if set is in cache
     */
    getContacts(force = false) {
      this.$log.debug('getContacts', {
        pageObject: this.currentPageObject,
        cached: this.getContactsFor(this.currentPageObject)
      })
      if (this.getContactsFor(this.currentPageObject) != null && !force) {
        // Page is already loaded in cache
        this.isLoadingContacts = false
        this.$nextTick(() => this.filterLoadSelection())
        return
      }

      if (this.isLoadingContacts) {
        return
      }

      // Load more contacts
      this.isLoadingContacts = true
      this.loadContacts(this.currentPageObject)
        .then(() => {
          this.isLoadingContacts = false
          this.$nextTick(() => {
            // Make sure these computed properties are up to date after vuex has loaded data
            this.$forceCompute('renderedItems')
            this.$forceCompute('renderedItemsCount')

            // Select All on next tick
            this.$nextTick(() => this.filterLoadSelection())
          })
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('error') })
          this.isLoadingContacts = false
        })
    },

    async createChatthread({
      mcontent = null,
      is_scheduled = false,
      deliver_at = null,
    }) {
      var error = false

      if ( this.selectedContactsCount === 0 ) {
        eventBus.$emit('popWarning', {
          title: this.$t('warnings.noContactSelectedTitle'),
          message: this.$t('warnings.noContactSelected')
        })
        error = true
      }

      if (!mcontent) {
        eventBus.$emit('popWarning', {
          title: this.$t('warnings.noContentTitle'),
          message: this.$t('warnings.noContent')
        })
        error = true
      }

      if (error) {
        return
      }

      const params = {
        originator_id: this.session_user.id,
        participants: this.selectedContacts.map(o => o.contact_id),
      }

      if ( mcontent ) {
        params.mcontent = mcontent
      }
      if ( is_scheduled ) {
        params.is_scheduled = true
        params.deliver_at = deliver_at
      }

      this.$log.debug('createChatthread', { params: params })
      const response = await axios.post( this.$apiRoute('chatthreads.store'), params )

      this.$router.push({ name: 'chatthreads.dashboard' })
      // %FIXME: clear MessageForm...can we just re-render the CreateThreadForm component to accomplish this?

    }, // createChatthread()

    // additional page loads
    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadNextPage() {
      if ( !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.getContacts()
      }
    },

    reloadFromFirstPage() {
      this.currentPage = 1
      this.getContacts()
    },

    inQuickAccessFilters(filter) {
      if (typeof filter === 'string') {
        return contains(this.pinned, filter)
      }
      return contains(this.pinned, filter.key)
    },

    //
    // Search
    //
    _doSearch() {
      this.isSearching = true
      this.axios.get(this.$apiRoute('mycontacts.search'), { params: { q: this.searchQuery } })
        .then(response => {
          if (this.searchQuery !== '') {
            this.SAVE_CONTACTS_LIST(response.data)
            this.searchResults = response.data.data.map(o => o.id)
            this.showSearchResults = true
            this.$nextTick(() => {
              this.selectIndeterminate = false
              this.selectAll = true
              this.UNSELECT_ALL()
              this.SELECT_CONTACTS(this.renderedItems)
            })
          }
          this.isSearching = false
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('search.error') })
          this.showSearchResults = false
          this.isSearching = false
        })
    },

    filterLoadSelection() {
      if (this.selectedFilter === 'all') {
        this.selectAll = false
        return
      }
      this.selectIndeterminate = false
      this.selectAll = true
      this.UNSELECT_ALL()
      this.SELECT_CONTACTS(this.renderedItems)
    },

  }, // methods

  /* ------------------------------------------------------------------------ */
  /*                                   WATCH                                  */
  /* ------------------------------------------------------------------------ */
  watch: {

    searchQuery(value) {
      // If cleared then unset search results
      if (value === '') {
        this.showSearchResults = false
        this.searchResults = []
        this.$nextTick(() => this.filterLoadSelection())
      } else {
        // Debounced search
        this.doSearch()
      }
    },

    selectAll(value) {
      if (this.contactsCount === 0) {
        return
      }

      // If some items are selected
      //   (selectIndeterminate = true and selectAll = false) then unselect all
      //   items and unset indeterminate and select all next tick
      if (this.selectIndeterminate || !value) {
        this.UNSELECT_ALL()
        this.$nextTick(() => {
          this.selectIndeterminate = false
          this.selectAll = false
        })
        return
      }

      // Select all renderedItems
      this.UNSELECT_ALL()
      this.SELECT_CONTACTS(this.renderedItems)
    },

    selectedFilter(value) {
      // New filter was selected, load it's contents from the first page
      this.reloadFromFirstPage()
    },

    session_user(value) {
      if (value) {
         // initial load only, depends on session user (synchronous)
        if (this.contactsCount === 0) {
          this.$log.debug('live-chat/CreateThread - watch session_user: reloadFromFirstPage()')
          this.reloadFromFirstPage()
        }
      }
    },

    sortBy(newVal) {
      this.$log.debug('live-chat/CreateThread - watch sortBy : reloadFromFirstPage()')
      this.$refs.sortCtrls.hide(true)
      this.reloadFromFirstPage()
    },

  }, // watch

}
</script>

<style lang="scss" scoped>
body {
  #view-create-thread {
    background-color: #fff;
  }

  .top-bar {
    border-bottom: 1px solid rgba(138,150,163,.25);
  }

}

.filter-controls {
  ::v-deep .dropdown-item {
    padding-left: 0;
  }
}

.tag-debug {
  border: solid orange 1px;
}
</style>

<style lang="scss">
body #view-createthread {
}

</style>

<i18n lang="json5">
{
  "en": {
    "error": "An Error occurred while loading your contacts",
    "filter": {
      "label": "Filters"
    },
    "filters": {
      "add": "Add New Filter",
      "all": "All",
      "canceled": "Canceled Subscribers",
      "expired": "Expired Subscribers",
      "followers": "Followers",
      "purchasers": "Post Purchasers",
      "subscribers": "Subscribers",
      "tippers": "Tippers"
    },
    "nav": {
      "return": "Back"
    },
    "no-results": "No Results",
    "search": {
      "error": "An Error has occurred during your search. Please try again later.",
      "label": "Send to:",
      "no-results": "No contacts found for search \"{search}\"",
      "searching": "Searching"
    },
    "selectAll": "Select All",
    "selectedCount": "({count} Selected)",
    "sort": {
      "alphabetical": "name"
    },
    "title": "New Message",
    "warnings": {
      "noContactSelected": "Please select at least one contact from your list of contacts.",
      "noContactSelectedTitle": "No Contacts Selected",
      "noContent": "Please provide a message to send.",
      "noContentTitle": "No Message"
    }
  }
}
</i18n>
