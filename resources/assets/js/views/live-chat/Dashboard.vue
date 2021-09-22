<template>
  <WithSidebar
    v-if="!isLoading"
    id="view-livechat"
    :class="{ mobile: mobile }"
    :focusMain="threadOpen"
    :isMessages="true"
    :hasBorder="true"
    removeMobileMainNavTop
  >
    <template #sidebar>
      <Sidebar :state="state" />
    </template>

    <transition mode="out-in" :name="mobile ? '' : 'quick-fade'" :key="activeThreadId">
      <router-view
        class="w-100"
        :key="activeThreadId"
        :session_user="session_user"
        :participant="participants(activeThread)"
        :timeline="activeThread ? activeThread.timeline : null"
        :currentNotes="activeThread ? activeThread.notes : null"
      />
      <div v-if="!activeThreadId" class="d-flex h-100 align-items-center justify-content-around">
        <div class="d-flex flex-column">
          <div class="h4 font-weight-bold mb-4">
            {{ $t('callToAction') }}
          </div>
          <div class="d-flex justify-content-around">
            <b-btn variant="primary" size="lg" :to="linkCreateThread()" class="font-size-larger">
              <fa-icon icon="plus" />
              {{ $t('newMessage') }}
            </b-btn>
          </div>
        </div>
      </div>
    </transition>
  </WithSidebar>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/Dashboard.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import _ from 'lodash'
import { eventBus } from '@/eventBus'
import SearchInput from '@components/common/search/HorizontalOpenInput'

import Sidebar from './components/Dashboard/Sidebar'

import WithSidebar from '@views/layouts/WithSidebar'

export default {
  name: 'LiveChatDashboard',

  components: {
    Sidebar,
    SearchInput,
    WithSidebar,
  },

  computed: {
    ...Vuex.mapState(['mobile']),
    ...Vuex.mapState('messaging', [ 'threads' ]),
    ...Vuex.mapGetters(['session_user', 'getUnreadMessagesCount']),

    activeThreadId() {
      return this.$route.params.id
    },
    activeThread() {
      return this.threads[this.activeThreadId] || {}
    },

    /**
     * If a thread is open
     */
    threadOpen() {
      return this.activeThreadId ? true : false
    },

    // renderedThreads() {
    //   if (this.showSearchResults) {
    //     return this.searchResults
    //   }
    //   return this.chatthreads || []
    // },

    isLoading() {
      return !this.session_user
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },


  }, // computed()

  data: () => ({

    state: 'loading',

    moment: moment,

    sortBy: 'recent',

    // chatthreads: null,

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
      if (!chatthread.participants) {
        return null
      }
      return _.find(chatthread.participants, participant => participant.id !== this.session_user.id)
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

    // async markAllRead() {
    //   await axios.post( this.$apiRoute('chatthreads.markAllRead') )

    //   // reset unread count for all threads
    //   this.chatthreads.forEach(thread => {
    //     thread.unread_count = 0
    //   })

    //   // reload total unread count
    //   this.getUnreadMessagesCount()
    // },

    // pageClickHandler(e, page) {
    //   this.currentPage = page
    //   this.getChatthreads()
    // },

    // may adjust filters, but always reloads from page 1
    // reloadFromFirstPage() {
    //   this.doReset()
    //   this.getChatthreads()
    // },

    // doReset() {
    //   this.renderedPages = []
    //   this.renderedItems = []
    //   this.isLastVisible = false
    //   this.isMoreLoading = true
    // },

    // toggles a 'boolean' filter
    // toggleFilter(k) { // keeps any filters set prior, adds new one
    //   if ( Object.keys(this.filters).includes(k) ) {
    //     delete this.filters[k]
    //   } else {
    //     this.filters[k] = 1
    //   }
    //   this.reloadFromFirstPage()
    // },

    // clearFilters() {
    //   this.filters = {}
    // },

    // _doSearch() {
    //   this.isSearching = true
    //   this.axios.get(this.$apiRoute('chatthreads.search'), { params: { q: this.searchQuery} })
    //     .then(response => {
    //       if (this.searchQuery !== '') {
    //         this.searchResults = response.data.data
    //         this.showSearchResults = true
    //       }
    //     })
    //     .catch(error => {
    //       eventBus.$emit('error', { error, message: this.$t('search.error') })
    //       this.showSearchResults = false
    //       this.isSearching = false
    //     })
    // }

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
        this.state = 'loaded'
        // if (!this.chatthreads) { // initial load only, depends on sesssion user (synchronous)
        //this.$log.debug('live-chat/Dashboard - watch session_user: reloadFromFirstPage()')
        // }
      }
    },

    sortBy (newVal) {
      //this.$log.debug('live-chat/Dashboard - watch sortBy : reloadFromFirstPage()')
      this.reloadFromFirstPage()
    },

    // activeThreadId(newVal) {
    //   if (newVal) {
    //     const activeThread = this.chatthreads.find( ct => ct.id === newVal )
    //     activeThread.unread_count = 0
    //   }
    // }

  }, // watch

}
</script>

<style lang="scss" scoped>
// thread view should be full screen on mobile
.content.thread #view-livechat.mobile {
  height: 100%;
}

#view-livechat {
  height: 100%;
  background-color: #fff;

  &.mobile {
    position: relative;
    top: 0;
    bottom: 0;
    padding-left: 0;
    padding-right: 0;
  }

  .chatthread-list {
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

</style>

<i18n lang="json5">
{
  "en": {
    "callToAction": "Select a Conversation or Send a New Message",
    "newMessage": "New Message",
  }
}
</i18n>
