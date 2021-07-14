<template>
  <div v-if="!isLoading" class="container-fluid px-3 py-3" id="view-livechat">

    <section class="row h-100" style="max-height: 100%;">

      <aside class="col-md-5 col-lg-4">

        <article class="top-bar d-flex justify-content-between align-items-center mb-3">
          <div class="h4" v-text="$t('header')" />
          <div class="d-flex">
            <b-button variant="link" class="clickme_to-schedule_message" @click="doSomething">
              <fa-icon :icon="['far', 'calendar-alt']" class="fa-lg" />
            </b-button>
            <b-button variant="link" class="clickme_to-send_message" :to="linkCreateThread()">
              <fa-icon :icon="['fas', 'plus']" size="lg" />
            </b-button>
          </div>
        </article>

        <Search v-model="searchQuery" :label="$t('search.label')" />

        <article class="chatthread-filters py-3 d-flex OFF-justify-content-between align-items-center">
          <!-- Filters/View -->
          <span class="mr-2" v-text="$t('filters.label')" />
          <b-form-select v-model="selectedFilter" :options="selectFilters" />

          <SortControl v-model="sortBy" />
        </article>
        <article class="d-flex">
          <b-btn variant="link" class="ml-auto" @click="markAllRead">Mark All as Read</b-btn>
        </article>

        <article class="chatthread-list">
          <b-list-group>
            <PreviewThread
              v-for="ct in renderedThreads"
              :key="ct.id"
              :to="linkChatthread(ct.id)"
              :active="isActiveThread(ct.id)"
              :data-ct_id="ct.id"
              class="px-2"
              :participant="participants(ct)"
              :chatthread="ct"
            />
            <b-list-group-item v-if="renderedThreads.length === 0" class="text-center">
              {{ showSearchResults ? $t('no-items-search', { query: searchQuery }) : $t('no-items') }}
            </b-list-group-item>
          </b-list-group>

          <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="perPage"
            aria-controls="threads-list"
            v-on:page-click="pageClickHandler"
            class="mt-3"
          ></b-pagination>
        </article>

      </aside>

      <main class="col-md-7 col-lg-8 h-100" style="max-height: 100%;">
        <transition mode="out-in" name="quick-fade" :key="activeThreadId">
          <router-view
            :key="activeThreadId"
            :session_user="session_user"
            :participant="participants(activeThread)"
          />
        </transition>
      </main>

    </section>

  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/Dashboard.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import _ from 'lodash'
import { eventBus } from '@/app'
import PreviewThread from '@views/live-chat/components/PreviewThread'
import SearchInput from '@components/common/search/HorizontalOpenInput'
import Search from '@views/live-chat/components/Search'
import SortControl from '@views/live-chat/components/SortControl'

export default {
  name: 'LiveChatDashboard',

  components: {
    PreviewThread,
    SearchInput,
    Search,
    SortControl,
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'getUnreadMessagesCount']),

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
            this.reloadFromFirstPage()
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

    renderedThreads() {
      if (this.showSearchResults) {
        return this.searchResults
      }
      return this.chatthreads || []
    },

    selectFilters() {
      return this.availableFilters.map(o => ({ value: o.key, text: o.label }))
    },

    isLoading() {
      return !this.session_user || !this.chatthreads
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },


  }, // computed()

  data: () => ({

    moment: moment,

    sortBy: 'recent',

    chatthreads: null,

    meta: null,
    perPage: 10,
    currentPage: 1,

    renderedItems: [],
    renderedPages: [], // track so we don't re-load same page (set of messages) more than 1x

    isLastVisible: false, // was: lastPostVisible
    isMoreLoading: true,

    filters: {},

    selectedFilter: 'all',

    searchQuery: '',
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

  methods: {

    ...Vuex.mapActions([
      'getMe',
    ]),

    linkChatthread(id) {
      return { name: 'chatthreads.show', params: { id: id } }
    },
    linkCreateThread() {
      return { name: 'chatthreads.create' }
    },


    participants(chatthread) { // other than session user
      // pop() because right now we only support 1-on-1 conversations (no group chats)
      return chatthread
        ? chatthread.participants.filter( u => u.id !== this.session_user.id ).pop()
        : null
    },

    isActiveThread(id) {
      return id === this.activeThreadId
    },
    isActiveContact(id) {
      return id === this.activeContactId
    },

    doSomething() {
      // stub placeholder for impl
    },

    async getChatthreads() {
      let params = {
        page: this.currentPage, 
        take: this.perPage,
        //participant_id: this.session_user.id,
      }
      params = { ...params, ...this.filters }
      this.$log.debug('getChatthreads', {
        filters: this.filters,
        params: params,
      })
      if ( this.sortBy ) {
        params.sortBy = this.sortBy
      }
      const response = await axios.get( this.$apiRoute('chatthreads.index'), { params } )
      this.chatthreads = response.data.data
      this.meta = response.data.meta
    },

    async markAllRead() {
      await axios.post( this.$apiRoute('chatthreads.markAllRead') )

      // reset unread count for all threads
      this.chatthreads.forEach(thread => {
        thread.unread_count = 0
      })

      // reload total unread count
      this.getUnreadMessagesCount()
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getChatthreads()
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset()
      this.getChatthreads()
    },

    doReset() {
      this.renderedPages = []
      this.renderedItems = []
      this.isLastVisible = false
      this.isMoreLoading = true
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

    _doSearch() {
      this.isSearching = true
      this.axios.get(this.$apiRoute('chatthreads.search'), { params: { q: this.searchQuery} })
        .then(response => {
          if (this.searchQuery !== '') {
            this.searchResults = response.data.data
            this.showSearchResults = true
          }
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('search.error') })
          this.showSearchResults = false
          this.isSearching = false
        })
    }

  }, // methods

  watch: {

    searchQuery(value) {
      // If cleared then unset search results
      if (value === '') {
        this.showSearchResults = false
        this.searchResults = []
      } else {
        // Debounced search
        this.doSearch()
      }
    },

    session_user(value) {
      if (value) {
        if (!this.chatthreads) { // initial load only, depends on sesssion user (synchronous)
          this.$log.debug('live-chat/Dashboard - watch session_user: reloadFromFirstPage()')
          this.reloadFromFirstPage()
        }
      }
    },

    selectedFilter(value) {
      const index = _.findIndex(this.availableFilters, o => o.key === value)
      if (index > -1 && typeof this.availableFilters[index].callback === 'function') {
        this.availableFilters[index].callback()
      }
    },

    sortBy (newVal) {
      this.$log.debug('live-chat/Dashboard - watch sortBy : reloadFromFirstPage()')
      this.reloadFromFirstPage()
    },

    activeThreadId(newVal) {
      if (newVal) {
        const activeThread = this.chatthreads.find( ct => ct.id === newVal )
        activeThread.unread_count = 0
      }
    }

  }, // watch

}
</script>

<style lang="scss" scoped>
#view-livechat {
  background-color: #fff;

  height: calc(100vh - 100px);

  .chatthread-list {
    height: calc(100vh - 350px);
    overflow: auto;

    .list-group-item.active {
      background: rgba(0,145,234,.06);
      color: inherit;
      border-top: none;
      border-left: 1px solid rgba(138,150,163,.25);
      border-right: 1px solid rgba(138,150,163,.25);
      border-bottom: 1px solid rgba(138,150,163,.25);
    }
  }

}

.top-bar {
  //display: flex;
  //align-items: center;
  //justify-content: space-between;
  //padding: 15px 4px 16px;
  border-bottom: 1px solid rgba(138,150,163,.25);
}
</style>

<i18n lang="json5">
{
  "en": {
    "header": "Messages",
    "filters": {
      "label": "View:",
      "labels": {
        "all": "All",
        "subscribers": "Subscribers",
        "unread": "Unread"
      },
    },
    "no-items": "No Chat Threads",
    "no-items-search": "No Chat Threads Found for \"{query}\"",
    "search": {
      "label": "Search:"
    }
  }
}
</i18n>
