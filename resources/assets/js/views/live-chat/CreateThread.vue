<template>
  <div v-if="!isLoading" class="container-xl px-3 py-3" id="view-create-thread">

    <section class="row">

      <aside class="col-md-5 col-lg-4">

        <article class="top-bar d-flex justify-content-between align-items-center">
          <h4>Contacts</h4>
          <div class="d-flex">
            <b-button variant="link" class="clickme_to-search_messages" @click="doSomething">
              <fa-icon :icon="['fas', 'search']" class="fa-lg" />
            </b-button>
          </div>
        </article>

        <article class="d-none">
          Search
        </article>

        <article class="mycontacts-sort py-3 d-flex justify-content-between align-items-center">
          <p class="my-0">{{ sortBy | ucfirst }}</p>
          <div class="">
            <b-dropdown ref="sortCtrls" variant="link" size="sm" class="" no-caret>
              <template #button-content>
                <fa-icon :icon="['fas', 'sort-amount-down']" class="fa-lg" />
              </template>
              <b-dropdown-form>
                <b-form-group label="">
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="recent">Recent</b-form-radio>
                  <b-form-radio v-model="sortBy" size="sm" name="sort-posts-by" value="oldest">Oldest</b-form-radio>
                </b-form-group>
                <b-dropdown-divider></b-dropdown-divider>
                <b-form-group label="">
                  <b-dropdown-item-button>Mark All as Read</b-dropdown-item-button>
                </b-form-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>
        </article>

        <article class="mycontacts-filters py-3 d-flex OFF-justify-content-between align-items-center">
          <b-button @click="clearFilters()" pill variant="outline-info" class="mx-1">All</b-button>
          <b-button @click="toggleFilter('is_subscriber')" pill :variant="Object.keys(this.filters).includes('is_subscriber') ? 'info' : 'outline-info'" class="mx-1">Subscribers</b-button>
          <b-button pill variant="outline-info" class="mx-1">
            <fa-icon :icon="['fas', 'plus']" class="fa-lg" />
          </b-button>
        </article>

        <article class="contact-list">
          <b-list-group>
            <b-list-group-item
              v-for="(c, idx) in mycontacts"
              :key="c.id"
              :data-ct_id="c.id"
              class="px-2"
            >
              <PreviewContact 
                :session_user="session_user"
                :contact="c.contact"
                v-on:select-contact="selectContact($event)"
                v-model="selectedContacts[c.id]"
              />
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
import Vuex from 'vuex'
import moment from 'moment'
import CreateThreadForm from '@views/live-chat/components/CreateThreadForm'
import PreviewContact from '@views/live-chat/components/PreviewContact'

export default {
  name: 'CreateThread',

  components: {
    CreateThreadForm,
    PreviewContact,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.mycontacts
    },


  }, // computed()

  data: () => ({

    moment: moment,

    sortBy: 'recent',

    mycontacts: null,

    // %FIXME: Not sure how to propagate this down and back up to an array of custom form components, see:
    // https://vuejs.org/v2/guide/components.html#Using-v-model-on-Components
    selectedContacts: [],

    meta: null,
    perPage: 10,
    currentPage: 1,

    renderedItems: [],
    renderedPages: [], // track so we don't re-load same page (set of messages) more than 1x

    isLastVisible: false, // was: lastPostVisible
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

    doSomething() {
      // stub placeholder for impl
    },

    selectContact({ contact, isSelected }) {
      // contact is the user pkid
      console.log('CreateThread: selected contact', {
        contact: contact,
        isSelected: isSelected,
      })
      if ( !isSelected ) {
        this.selectedContacts = this.selectedContacts.filter( iter => iter !== contact )
      } else {
        this.selectedContacts.push(contact)
      }
    },

    async getContacts() {
      let params = {
        page: this.currentPage, 
        take: this.perPage,
        //participant_id: this.session_user.id,
      }
      params = { ...params, ...this.filters }
      console.log('getContacts', {
        filters: this.filters,
        params: params,
      })
      if ( this.sortBy ) {
        params.sortBy = this.sortBy
      }
      const response = await axios.get( this.$apiRoute('mycontacts.index'), { params } )
      this.mycontacts = response.data.data
      this.meta = response.meta
    },

    async createChatthread({ 
      mcontent = null,
      is_scheduled = false,
      deliver_at = null,
    }) {
      const params = {
        originator_id: this.session_user.id,
        participants: this.selectedContacts,
      }

      if ( mcontent ) {
        params.mcontent = mcontent
      }
      if ( is_scheduled ) {
        params.is_scheduled = true
        params.deliver_at = deliver_at
      }

      console.log('createChatthread', { params: params })
      const response = await axios.post( this.$apiRoute('chatthreads.store'), params )

      this.selectedContacts = [] 
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

  }, // methods

  watch: {

    session_user(value) {
      if (value) {
        if (!this.mycontacts) { // initial load only, depends on sesssion user (synchronous)
          console.log('live-chat/CreateThread - watch session_user: reloadFromFirstPage()')
          this.reloadFromFirstPage()
        }
      }
    },

    sortBy (newVal) {
      console.log('live-chat/CreateThread - watch sortBy : reloadFromFirstPage()')
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

.tag-debug {
  border: solid orange 1px;
}
</style>

<style lang="scss">
body #view-createthread {
}

</style>
