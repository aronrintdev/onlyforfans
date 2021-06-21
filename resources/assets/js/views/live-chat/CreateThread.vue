<template>
  <div v-if="!isLoading" class="container-xl px-3 py-3" id="view-create-thread">

    <section class="row">

      <aside class="col-md-5 col-lg-4">

        <article class="top-bar d-flex justify-content-between align-items-center">
          <div class="h4" v-text="$t('title')" />
        </article>

        <article class="mycontacts-sort pt-3 d-flex flex-column justify-content-between align-items-center">
          <div class="search d-flex align-items-center mb-2 w-100">
            <label for="create-thread-search" v-text="$t('search.label')" class="text-nowrap mr-2 mb-0" />
            <b-input ref="searchInput" id="create-thread-search" v-model="searchQuery" class="flex-grow-1" />
            <b-btn variant="link" @click="$refs.searchInput.$el.focus()">
              <fa-icon icon="search" size="lg" />
            </b-btn>
          </div>

          <div class="d-flex pt-3 pb-2 w-100">
            <!-- Quick Filters Buttons -->
            <article class="d-flex flex-wrap align-items-center flex-grow-1">
              <FilterSelect
                v-for="filter in quickAccessFiltersList"
                :key="filter.key"
                :label="filter.label"
                :selected="selectedFilter === filter.key"
                selectedVariant="secondary"
                variant="light"
                @selected="() => {
                  filter.callback()
                  selectedFilter = filter.key
                }"
                class="mx-1 my-1"
              />
              <FilterSelect
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
                <fa-icon icon="filter" />
              </template>
              <b-dropdown-item
                v-for="filter in availableFilters"
                :key="filter.key"
                :active="filter.key === selectedFilter"
                @click="() => {
                  filter.callback()
                  selectedFilter = filter.key
                }"
              >
                <fa-icon
                  :style="{ opacity: filter.always || inQuickAccessFilters(filter) ? '100%': '0' }"
                  icon="check"
                  class="mx-2"
                  size="lg"
                  fixed-width
                />
                {{ filter.label }}
              </b-dropdown-item>
              <b-dropdown-divider />
              <b-dropdown-item>
                <fa-icon icon="plus" class="text-success mx-2" size="lg" fixed-width />
                {{ $t('filters.add') }}
              </b-dropdown-item>
            </b-dropdown>

            <!-- Sort Control Dropdown -->
            <b-dropdown ref="sortCtrls" variant="link" size="sm" right no-caret>
              <template #button-content>
                <fa-icon :icon="['fas', 'sort-amount-down']" class="fa-lg" />
              </template>
              <b-dropdown-form>
                <b-form-group label="">
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="recent">Recent</b-form-radio>
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="oldest">Oldest</b-form-radio>
                </b-form-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>
        </article>

        <article class="contact-list position-relative">
          <!-- Select All -->
          <div class="mb-2 d-flex justify-content-end align-items-center">
            <span
              v-if="contactsSelectedLength > 0"
              class="text-muted mr-3 select-none"
              v-text="$t('selectedCount', { count: contactsSelectedLength })"
            />

            <label for="select-all" v-text="$t('selectAll')" class="mr-2 mb-0 select-none" />
            <b-form-checkbox
              id="select-all"
              v-model="selectAll"
              :value="true"
              :indeterminate="selectIndeterminate"
              inline
              class="mr-2 mb-1"
              size="lg"
            />
          </div>

          <b-list-group>
            <b-list-group-item
              v-for="contact in renderedItems"
              :key="contact.id"
              :data-ct_id="contact.id"
              class="px-2"
            >
              <PreviewContact :contact="contact" @input="onContactInput" />
            </b-list-group-item>
            <LoadingOverlay :loading="isSearching" :text="$t('search.searching')" />
            <b-list-group-item v-if="showSearchResults && searchResultsLength === 0" class="text-center">
              {{ $t('search.no-results', { search: searchQuery }) }}
            </b-list-group-item>
            <b-list-group-item v-else-if="renderedItems.length === 0" class="text-center">
              {{ $t('search.no-results', { search: searchQuery }) }}
            </b-list-group-item>
          </b-list-group>
        </article>

      </aside>

      <main class="col-md-7 col-lg-8">
          <CreateThreadForm
            :session_user="session_user"
            v-on:create-chatthread="createChatthread($event)"
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
import moment from 'moment'
import _ from 'lodash'

import CreateThreadForm from '@views/live-chat/components/CreateThreadForm'
import FilterSelect from './components/FilterSelect.vue'
import PreviewContact from '@views/live-chat/components/PreviewContact'

import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: 'CreateThread',

  components: {
    CreateThreadForm,
    FilterSelect,
    LoadingOverlay,
    PreviewContact,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.mycontacts
    },

    availableFilters() {
      return [
        {
          key: 'all',
          always: true,
          label: this.$t('filters.all'),
          callback: this.setFilter,
        }, {
          key: 'subscribers',
          label: this.$t('filters.subscribers'),
          callback: () => this.setFilter('is_subscriber'),
        }, {
          key: 'followers',
          label: this.$t('filters.followers'),
          callback: () => this.setFilter('is_follower'),
        }, {
          key: 'canceled',
          label: this.$t('filters.canceled'),
          callback: () => this.setFilter('is_cancelled_subscriber'),
        }, {
          key: 'expired',
          label: this.$t('filters.expired'),
          callback: () => this.setFilter('is_expired_subscriber'),
        }, {
          key: 'purchasers',
          label: this.$t('filters.purchasers'),
          callback: () => this.setFilter('has_purchased_post'),
        }, {
          key: 'tippers',
          label: this.$t('filters.tippers'),
          callback: () => this.setFilter('has_tipped'),
        },
      ]
    },

    quickAccessFiltersList() {
      return _.filter(this.availableFilters, o => (
        o.always || this.inQuickAccessFilters(o) || o.key === this.selectedFilter
      ))
    },

    contactsSelectedLength() {
      return _.filter(this.mycontacts, o => (o.selected)).length
    },

    contactsLength() {
      return Object.keys(this.mycontacts).length
    },

    searchResultsLength() {
      return Object.keys(this.searchResults).length
    },

    renderedItems() {
      if (this.showSearchResults) {
        return _.filter(this.mycontacts, o => _.indexOf(this.searchResults, o.id) > -1)
      }
      return this.mycontacts
    }

  }, // computed()

  data: () => ({

    moment: moment,

    sortBy: 'recent',

    mycontacts: {},

    // %FIXME: Not sure how to propagate this down and back up to an array of custom form components, see:
    // https://vuejs.org/v2/guide/components.html#Using-v-model-on-Components
    // selectedContacts: [],

    meta: null,
    perPage: 10,
    currentPage: 1,

    renderedPages: [], // track so we don't re-load same page (set of messages) more than 1x

    isLastVisible: false, // was: lastPostVisible
    isMoreLoading: true,

    // Search Items
    searchQuery: '',
    isSearching: false,
    showSearchResults: false,
    searchResults: [],
    searchDebounceDuration: 500,

    selectAll: false,
    selectIndeterminate: false,

    selectedFilter: 'all',
    quickAccessFilters: [
      'subscribers',
      'followers',
    ],

    filters: {},

  }), // data

  created() {
    this.getMe()
    this.doSearch = _.debounce(this._doSearch, this.searchDebounceDuration);
  },

  mounted() { },

  methods: {
    ...Vuex.mapActions([
      'getMe',
    ]),

    onContactInput(value) {
      Vue.set(this.mycontacts, value.id, value)
      const selected = _.filter(this.mycontacts, o => (o.selected)).length

      if ( this.contactsSelectedLength < this.contactsLength ) {
        this.selectIndeterminate = true
      }
      if ( this.contactsSelectedLength === this.contactsLength ) {
        this.selectIndeterminate = false
        this.selectAll = true
      }
      if (this.contactsSelectedLength === 0) {
        this.selectIndeterminate = false
        this.selectAll = false
      }
    },

    async getContacts() {
      let params = {
        page: this.currentPage,
        take: this.perPage,
        //participant_id: this.session_user.id,
      }
      params = { ...params, ...this.filters }
      this.$log.debug('getContacts', {
        filters: this.filters,
        params: params,
      })
      if ( this.sortBy ) {
        params.sortBy = this.sortBy
      }
      const response = await axios.get( this.$apiRoute('mycontacts.index'), { params } )

      const selected = _.keys(this.filters).length > 0 ? true : false

      this.mycontacts = _.keyBy(response.data.data.map(o => ({ ...o, selected })), 'id')

      if (selected) {
        this.selectAll = true
        this.selectIndeterminate = false
      } else {
        this.selectAll = false
      }
      this.meta = response.meta
    },

    async createChatthread({
      mcontent = null,
      is_scheduled = false,
      deliver_at = null,
    }) {
      const params = {
        originator_id: this.session_user.id,
        participants: _.filter(this.participants, selected).map(o => (o.id)),
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
      if ( !this.isMoreLoading && !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.isMoreLoading = true;
        this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.getContacts()
      }
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset()
      this.getContacts()
    },

    doReset() {
      this.renderedPages = []
      this.isLastVisible = false
      this.isMoreLoading = true
    },

    //
    // Filters
    //
    filterAdd() {
      //
    },

    setFilter(k) {
      this.clearFilters()
      if(k) {
        this.filters[k] = 1
      }
      this.reloadFromFirstPage()
    },

    // toggles a 'boolean' filter
    toggleFilter(k) { // keeps any filters set prior, adds new one
      if ( Object.keys(this.filters).includes(k) ) {
        delete this.filters[k]
      } else {
        this.filters[k] = 1
      }
      this.reloadFromFirstPage()
    },

    clearFilters() {
      this.filters = {}
    },

    inQuickAccessFilters(filter) {
      if (typeof filter === 'string') {
        return _.indexOf(this.quickAccessFilters, filter) > -1
      }
      return _.indexOf(this.quickAccessFilters, filter.key) > -1
    },

    //
    // Search
    //
    _doSearch() {
      this.isSearching = true
      this.axios.get(this.$apiRoute('mycontacts.search'), { params: { q: this.searchQuery } })
        .then(response => {
          if (this.searchQuery !== '') {
            this.mycontacts = { ...this.mycontacts, ..._.keyBy(response.data.data, 'id') }
            this.searchResults = response.data.data.map(o => o.id)
            this.showSearchResults = true
          }
          this.isSearching = false
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('search.error') })
          this.showSearchResults = false
          this.isSearching = false
        })
    },
  }, // methods

  watch: {

    searchQuery(value) {
      if (value === '') {
        this.showSearchResults = false
        this.searchResults = []
      } else {
        this.doSearch()
      }
    },

    selectAll(value) {
      if (this.contactsLength === 0) {
        return
      }

      if (this.selectIndeterminate || !value) {
        for(var index in this.mycontacts) {
          Vue.set(this.mycontacts, index, { ...this.mycontacts[index], selected: false })
        }
        this.$nextTick(() => {
          this.selectIndeterminate = false
          this.selectAll = false
        })
        return
      }

      for(var index in this.mycontacts) {
        Vue.set(this.mycontacts, index, { ...this.mycontacts[index], selected: true })
      }
    },

    session_user(value) {
      if (value) {
        if (this.contactsLength === 0) { // initial load only, depends on sesssion user (synchronous)
          this.$log.debug('live-chat/CreateThread - watch session_user: reloadFromFirstPage()')
          this.reloadFromFirstPage()
        }
      }
    },

    sortBy (newVal) {
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
    "title": "New Message"
  }
}
</i18n>
