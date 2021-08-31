<template>
  <div v-if="!isLoading" class="container-fluid" id="component-stats_dashboard">

    <section>

      <b-card>

        <h1>Mass Message Stats</h1>
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
              <span v-if="data.item.mediafile_counts.images" class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ data.item.mediafile_counts.images }}</span>
              <span v-if="data.item.mediafile_counts.videos" class="ml-2"><fa-icon :icon="['far', 'video']" class="fa-sm" fixed-width /> {{ data.item.mediafile_counts.videos }}</span>
              <span v-if="data.item.mediafile_counts.audios" class="ml-2"><fa-icon :icon="['far', 'microphone']" class="fa-sm" fixed-width /> {{ data.item.mediafile_counts.audios }}</span>
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
        { key: 'created_at', label: 'Created', formatter: v => Vue.options.filters.niceDateTime(v, false) },
        { key: 'mcontent', label: 'Text', tdClass: 'tag-col-mcontent' },
        { key: 'mediafile_counts', label: 'Attachment', /*formatter: v => Array.isArray(v) ? v.length : ''*/ },
        { key: 'price', label: 'Price', formatter: v => Vue.options.filters.niceCurrency(v) },
        { key: 'is_delivered', label: 'Sent (Delivered?)', formatter: v => Vue.options.filters.niceBool(v) },
        { key: 'is_read', label: 'Viewed (Read?)', formatter: v => Vue.options.filters.niceBool(v) },
        { key: 'purchase', label: 'Purchased ??', formatter: v => 'tbd' },
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

  }), // data

  methods: {

    //...Vuex.mapActions([ 'getMe', ]),

    async getStats() {
      const params = { take: this.itemsPerPage, page: this.currentPage }
      let response
      try { 
        response = await this.axios.get( this.$apiRoute('chatmessages.index'), { params } )
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
