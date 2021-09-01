<template>
  <div v-if="!isLoading" class="container-fluid" id="component-stats_dashboard">

    <section>

      <b-card>

        <h1>Mass Message Stats</h1>

        <section class="crate-filters mb-3 d-flex">
          <!-- filters -->
            <!--
          <div class="box-filter p-3">
            <h6>Boolean</h6>
            <b-button v-for="(f,idx) in postFilters.booleans" :key="idx" @click="toggleFilter('booleans', f)" :variant="f.is_active ? 'primary' : 'outline-primary'" class="mr-3">{{ f.label }}</b-button>
          </div>
          -->
          <div class="box-filter p-3">
            <h6>Search</h6>
            <b-form-input v-model="filters.qsearch" placeholder="Enter search text"></b-form-input>
          </div>
        </section>

        <b-pagination v-model="currentPage" :total-rows="totalItems" :per-page="itemsPerPage" aria-controls="earnings-transactions-table" />
        <b-table
          responsive
          id="mass-message-stats-table"
          :items="messagestats"
          :fields="fields"
          :current-page="currentPage"
        >
          <template #cell(mcontent)="data">
            <ContentViewer :str=data.item.mcontent />
          </template>

          <template #cell(mediafile_counts)="data">
            <div class="d-flex">
              <!--
              <span class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ JSON.stringify(data.item.attachment_counts) }}</span>
              -->
              <span v-if="data.item.attachment_counts.images_count" class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.images_count }}</span>
              <span v-if="data.item.attachment_counts.videos_count" class="ml-2"><fa-icon :icon="['far', 'video']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.videos_count }}</span>
              <span v-if="data.item.attachment_counts.audios_count" class="ml-2"><fa-icon :icon="['far', 'microphone']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.audios_count }}</span>
            </div>
          </template>
        </b-table>
        <b-pagination v-model="currentPage" :total-rows="totalItems" :per-page="itemsPerPage" aria-controls="earnings-transactions-table" />

      </b-card>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import Vue from 'vue'
import moment from 'moment'
import { eventBus } from '@/eventBus'
import ContentViewer from './ContentViewer'

export default {
  computed: {
    //...Vuex.mapState(['mobile']),
    //...Vuex.mapState('messaging', [ 'threads' ]),
    //...Vuex.mapGetters(['session_user', 'getUnreadMessagesCount']),

    isLoading() {
      return false // !this.session_user
    },

    /*
    totalRows() {
      return this.meta ? this.meta.total : 1
    },
     */
    fields() {
      return [
        { key: 'created_at', label: 'Date', formatter: v => Vue.options.filters.niceDateTime(v, false), sortable: true },
        { key: 'mcontent', label: 'Text', tdClass: 'tag-col-mcontent', },
        { key: 'mediafile_counts', label: 'Attachment', },
        { key: 'price', label: 'Price', formatter: v => Vue.options.filters.niceCurrency(v), sortable: true },
        //{ key: 'deliver_at', label: 'Delivered At', formatter: v => Vue.options.filters.niceBool(v) },
        { key: 'sent_count', label: 'Sent', },
        { key: 'read_count', label: 'Viewed (Read?)', },
        { key: 'purchased_count', label: 'Purchased', },
      ]
    },


  }, // computed()

  data: () => ({

    moment: moment,
    currentPage: 1,
    itemsPerPage: 20,
    totalItems: 0,
    totalPages: 0,
    messagestats: [],

    filters: {
      /*
      booleans: [
        { key: 'is_flagged', label: 'Reported', is_active: false, }, 
      ],
       */
      qsearch: null, // search query string
      //start_date: null,
      //end_date: null,
    },

  }), // data

  methods: {

    //...Vuex.mapActions([ 'getMe', ]),

    async getStats() {
      const params = { take: this.itemsPerPage, page: this.currentPage }
      if (this.qsearch) {
        params.qsearch = this.qsearch
      }
      let response
      try { 
        response = await this.axios.get( this.$apiRoute('chatmessagegroups.index'), { params } )
        this.messagestats = response.data.data
        this.totalPages = response.data.meta.last_page
        this.totalItems = response.data.meta.total
        //this.transactionsLoading = false
      } catch (err) {
        //eventBus.$emit('error', { error, message: this.$t('error.load') })
        //this.transactionsLoading = false
      }
    },

    truncated(str) {
      // ref: https://stackoverflow.com/questions/5454235/shorten-string-without-cutting-words-in-javascript
      //return str.replace(/^(.{3}[^\s]*).*/, "$1")

      const maxLength = 150 // maximum number of characters to extract
      let trimmedString = str.substr(0, maxLength) //trim the string to the maximum length
      return trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) //re-trim if we are in the middle of a word
    },

  },

  created() { 
    //this.getMe()
    this.getStats()
  },

  mounted() { },

  watch: { 
    currentPage(value) {
      this.getStats()
    }
  }, // watch

  name: 'StatsDashboard',

  components: {
    ContentViewer,
  },


}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
::v-deep .tag-col-mcontent {
  max-width: 20rem;
}
td { 
}
</style>
