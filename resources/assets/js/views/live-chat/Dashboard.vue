<template>
  <div v-if="!isLoading" class="container-xl px-3 py-3" id="view-livechat">

    <section class="row">

      <aside class="col-md-5 col-lg-4">

        <article class="top-bar d-flex justify-content-between align-items-center">
          <h4>Messages</h4>
          <div class="d-flex">
            <b-button variant="link" class="clickme_to-search_messages" @click="doSomething">
              <fa-icon :icon="['fas', 'search']" class="fa-lg" />
            </b-button>
            <b-button variant="link" class="clickme_to-schedule_message" @click="doSomething">
              <fa-icon :icon="['far', 'calendar-alt']" class="fa-lg" />
            </b-button>
            <b-button variant="link" class="clickme_to-send_message" @click="doSomething">
              <fa-icon :icon="['fas', 'plus']" class="fa-lg" />
            </b-button>
          </div>
        </article>

        <article class="d-none">
          Search
        </article>

        <article class="chatthread-sort py-3 d-flex justify-content-between align-items-center">
          <p class="my-0">Unread First</p>
          <div class="">
            <b-dropdown ref="sortCtrls" variant="link" size="sm" class="" no-caret>
              <template #button-content>
                <fa-icon :icon="['fas', 'sort-amount-down']" class="fa-lg" />
              </template>
              <b-dropdown-form>
                <b-form-group label="">
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="recent">Recent</b-form-radio>
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="oldest">Oldest</b-form-radio>
                  <!--
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="unread-first">Unread First</b-form-radio>
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="oldest-unread-first">Oldest Unread First</b-form-radio>
                  -->
                </b-form-group>
                <b-dropdown-divider></b-dropdown-divider>
                <b-form-group label="">
                  <b-dropdown-item-button>Mark All as Read</b-dropdown-item-button>
                </b-form-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>
        </article>

        <article class="chatthread-filters py-3 d-flex OFF-justify-content-between align-items-center">
           <b-button @click="clearFilters()" pill variant="outline-info" class="mx-1">All</b-button>
           <b-button @click="toggleFilter('is_unread')" pill :variant="Object.keys(this.filters).includes('is_unread') ? 'info' : 'outline-info'" class="mx-1">Unread</b-button>
           <b-button @click="toggleFilter('is_subscriber')" pill variant="outline-info" class="mx-1">Subscribers</b-button>
           <b-button pill variant="outline-info" class="mx-1">
              <fa-icon :icon="['fas', 'plus']" class="fa-lg" />
           </b-button>
        </article>

        <article class="chatthread-list">
          <b-list-group>
            <b-list-group-item
              v-for="(ct, idx) in chatthreads"
              :key="ct.id"
              :to="link(ct.id)"
              :active="isActiveThread(ct.id)"
              :data-ct_id="ct.id"
              class="px-2"
            >
              <PreviewThread 
                :session_user="session_user"
                :participant="participants(ct)"
                :chatthread="ct"
              />
            </b-list-group-item>
          </b-list-group>
        </article>

      </aside>

      <main class="col-md-7 col-lg-8">
        <transition mode="out-in" name="quick-fade">
          <router-view 
            :session_user="session_user" 
            :participant="participants(activeThread)"
          />
        </transition>
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'
import PreviewThread from '@views/live-chat/components/PreviewThread'

export default {
  name: 'LivechatDashboard',

  components: {
    PreviewThread,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    activeThreadId() {
      return this.$route.params.id
    },

    activeThread() {
      return this.chatthreads.find( ct => ct.id === this.activeThreadId )
    },

    isLoading() {
      return !this.session_user || !this.chatthreads
    },

    routes() {
      let routes = [
        {
          name: 'ListScheduled',
          to: { name: 'chatmessages.scheduled', params: {} },
        }, 
        {
          name: 'CreateThread',
          to: { name: 'chatthreads.create', params: {} },
        }, 
        {
          name: 'Gallery',
          to: { name: 'chatthreads.gallery', params: {} },
        }, 
        {
          name: 'ShowThread',
          to: { name: 'chatthreads.show', params: {} },
        }, 
      ]

      routes = routes.map(route => ({
        ...route,
        active: this.isActiveRoute(route)
      }))

      return routes
    }

  },

  data: () => ({

    moment: moment,

    sortBy: null, // %TODO

    chatthreads: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

    renderedItems: [],
    renderedPages: [], // track so we don't re-load same page (set of messages) more than 1x

    isLastMsgVisible: false, // was: lastPostVisible
    isMoreLoading: true,

    filters: {},

  }), // data

  created() { 
    this.getMe()
  },

  mounted() { },

  methods: {

    ...Vuex.mapActions([
      'getMe',
    ]),

    link(id) {
      return { name: 'chatthreads.show', params: { id: id } }
    },

    participants(chatthread) { // other than session user
      // pop() because right now we only support 1-on-1 conversations (no group chats)
      return chatthread
        ?  chatthread.participants.filter( u => u.id !== this.session_user.id ).pop()
        : null
    },

    isActiveThread(id) {
      //return id === this.$route.params.id
      return id === this.activeThreadId
    },

    doSomething() {
      // stub placeholder for impl
    },

    async getChatthreads() {
      let params = {
        page: this.currentPage, 
        take: this.perPage,
        participant_id: this.session_user.id,
      }
      params = { ...params, ...this.filters }
      console.log('getChatthreads', {
        filters: this.filters,
        params: params,
      })
      if ( this.sortBy ) {
        params.sortBy = this.sortBy
      }
      const response = await axios.get( this.$apiRoute('chatthreads.index'), { params } )
      this.chatthreads = response.data.data
      this.meta = response.meta
    },

    isActiveRoute(route) {
      if (this.$router.currentRoute.name === route.to.name) {
        return true
      }
      for (var matched of this.$router.currentRoute.matched) {
        if (matched.name === route.to.name) {
          return true
        }
      }
      return false
    },

    // additional page loads
    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadNextPage() {
      if ( !this.isMoreLoading && !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.isMoreLoading = true;
        this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.getChatthreads()
      }
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset()
      this.getChatthreads()
    },

    doReset() {
      this.renderedPages = []
      this.renderedItems = []
      this.isLastMsgVisible = false
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

  }, // methods

  watch: {

    $route() {
      this.$forceCompute('routes')
    },

    session_user(value) {
      if (value) {
        if (!this.chatthreads) { // initial load only, depends on sesssion user (synchronous)
          console.log('live-chat/Dashboard - watch session_user: reloadFromFirstPage()')
          //this.getChatthreads()
          this.reloadFromFirstPage()
        }
      }
    },

    sortBy (newVal) {
      console.log('live-chat/Dashboard - watch sortBy : reloadFromFirstPage()')
      this.$refs.sortCtrls.hide(true)
      this.reloadFromFirstPage()
    },

  }, // watch

}
</script>

<style lang="scss" scoped>
#view-livechat {
  background-color: #fff;
}

.top-bar {
  //display: flex;
  //align-items: center;
  //justify-content: space-between;
  //padding: 15px 4px 16px;
  border-bottom: 1px solid rgba(138,150,163,.25);
}

</style>

<style lang="scss">
body #view-livechat {
  .btn-link:hover {
    text-decoration: none;
  }
  .btn-link:focus, .btn-link.focus, .btn:focus, .btn.focus {
    box-shadow: none !important;
    text-decoration: none !important;
  }
  // disable BS4's hover effect as it's indistinguishable from 'pressed' state
  .chatthread-filters .btn:hover {
    color: inherit !important;
    background-color: inherit !important;
    border-color: inherit !important;
  }
}

</style>
