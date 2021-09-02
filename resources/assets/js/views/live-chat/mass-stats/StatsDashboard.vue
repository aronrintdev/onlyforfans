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
        </section>

        <section class="crate-pagination d-sm-flex flex-row-reverse align-items-center mb-3">
          <div class="box-search ml-auto mb-3 mb-sm-0">
            <b-form-input v-model="filters.qsearch" class="" placeholder="Enter search text"></b-form-input>
          </div>
          <div v-if="!mobile" class="box-pagination d-flex align-items-center">
            <b-pagination v-model="currentPage" :total-rows="totalItems" :per-page="itemsPerPage" class="m-0" aria-controls="earnings-transactions-table" />
            <div class="ml-3">({{ totalItems }})</div>
          </div>
        </section>

        <b-table v-if="!mobile"
          responsive
          id="mass-message-stats-table"
          :items="chatmessagegroups"
          :fields="fields"
          :current-page="currentPage"
          :sort-by="sortBy"
          :sort-desc="sortDesc"
          @sort-changed="sortHandler"
        >
          <template #cell(mcontent)="data">
            <ContentViewer :str=data.item.mcontent />
          </template>

          <template #cell(mediafile_counts)="data">
            <div class="d-flex">
              <span v-if="data.item.attachment_counts.images_count" class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.images_count }}</span>
              <span v-if="data.item.attachment_counts.videos_count" class="ml-2"><fa-icon :icon="['far', 'video']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.videos_count }}</span>
              <span v-if="data.item.attachment_counts.audios_count" class="ml-2"><fa-icon :icon="['far', 'microphone']" class="fa-sm" fixed-width /> {{ data.item.attachment_counts.audios_count }}</span>
            </div>
          </template>
        </b-table>
        
        <section v-if="!mobile" class="crate-pagination d-flex align-items-center my-3">
          <b-pagination v-model="currentPage" :total-rows="totalItems" :per-page="itemsPerPage" class="m-0" aria-controls="earnings-transactions-table" />
          <div class="ml-3">({{ totalItems }})</div>
        </section>

        <section v-if="mobile" class="crate-as_cards"> <!-- responsive 'table' using cards -->
          <b-card v-for="(cmg,idx) in chatmessagegroups" :key="idx" v-observe-visibility="idx===chatmessagegroups.length-1 ? visibilityChanged : false" class="mb-3">
            <b-list-group flush>
              <b-list-group-item><span class="tag-label">Date:</span> {{ cmg.created_at | niceDateTime(false) }}</b-list-group-item>
              <b-list-group-item><span class="tag-label">Text:</span> <ContentViewer :str=cmg.mcontent /></b-list-group-item>
              <b-list-group-item><span class="tag-label">Attachment:</span>
                  <span v-if="cmg.attachment_counts.images_count" class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.images_count }}</span>
                  <span v-if="cmg.attachment_counts.videos_count" class="ml-2"><fa-icon :icon="['far', 'video']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.videos_count }}</span>
                  <span v-if="cmg.attachment_counts.audios_count" class="ml-2"><fa-icon :icon="['far', 'microphone']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.audios_count }}</span>
              </b-list-group-item>
              <b-list-group-item><span class="tag-label">Price:</span> {{ cmg.price | niceCurrency }}</b-list-group-item>
              <b-list-group-item><span class="tag-label">Sent:</span> {{ cmg.sent_count }}</b-list-group-item>
              <b-list-group-item><span class="tag-label">Viewed:</span> {{ cmg.read_count }}</b-list-group-item>
              <b-list-group-item><span class="tag-label">Purchased:</span> {{ cmg.purchased_count }}</b-list-group-item>
            </b-list-group>
          </b-card>

          <div v-if="!isScrollingViewFullyLoaded" class="d-flex justify-content-center align-content-center">
            <b-button @click="loadMore" variant="primary" class="w-100" :disabled="isScrollingViewFullyLoaded||isDataLoading">
              <fa-icon v-if="isDataLoading" icon="spinner" size="2x" fixed-width spin />
              <span v-else>Load More</span>
            </b-button>
          </div>

          <b-card v-if="!isDataLoading && isScrollingViewFullyLoaded" class="mt-3" >
            <div class="d-flex justify-content-center align-content-center OFF-my-4">
              {{ $t('endMessage') }}
            </div>
          </b-card>

        </section>

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
    ...Vuex.mapState(['mobile']),

    isLoading() {
      return false // !this.session_user
    },

    isScrollingViewFullyLoaded() { // for mobile view, are all available items loaded?
      return !( this.chatmessagegroups.length < this.totalItems )
    },

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
    sortBy: 'created_at',
    sortDesc: false,
    chatmessagegroups: [],
    iisDataLoading: false,

    filters: {
      qsearch: null, // search query string
    },

  }), // data

  methods: {

    async getData(page, take) {

      const params = { 
        take: take, 
        page: page, 
        sortBy: this.sortBy,
        sortDir: this.sortDesc ? 'desc' : 'asc',
      }

      if (this.filters.qsearch) {
        params.qsearch = this.filters.qsearch
      }

      try { 
        this.isDataLoading = true
        const response = await this.axios.get( this.$apiRoute('chatmessagegroups.index'), { params } )
        if (this.mobile) { // scrolling
          const items = [...this.chatmessagegroups, ...response.data.data]
          this.chatmessagegroups = items
        } else { // paged
          this.chatmessagegroups = response.data.data
        }
        this.totalPages = response.data.meta.last_page
        this.totalItems = response.data.meta.total
        this.isDataLoading = false
      } catch (err) {
        //eventBus.$emit('error', { error, message: this.$t('error.load') })
        this.isDataLoading = false
      }
    },

    async loadMore() {
      if ( this.currentPage < this.totalPages ) {
        this.currentPage += 1
      }
    },

    sortHandler(context) {
      this.sortBy = context.sortBy
      this.sortDesc = context.sortDesc
      this.currentPage = 1 // reset to first page
      this.getData(this.currentPage, this.itemsPerPage)
    },

    truncated(str) {
      // ref: https://stackoverflow.com/questions/5454235/shorten-string-without-cutting-words-in-javascript
      const maxLength = 150 // maximum number of characters to extract
      let trimmedString = str.substr(0, maxLength) //trim the string to the maximum length
      return trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) //re-trim if we are in the middle of a word
    },

    visibilityChanged(isVisible) {
      //this.lastTransactionVisible = isVisible
      if ( isVisible && !this.isDataLoading && !this.isScrollingViewFullyLoaded ) { 
        this.loadMore()
      }
    }

  },

  created() { 
    if (this.mobile) { 
      this.itemsPerPage = 5
    }
    this.getData(1, this.itemsPerPage)
  },

  mounted() { },

  watch: { 
    currentPage(value) {
      this.getData(value, this.itemsPerPage)
    },

    'filters.qsearch': function (n, o) {
      if ( n === o ) {
        return
      }
      this.getData(this.currentPage, this.itemsPerPage)
    },

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
.crate-as_cards {
  .tag-label {
    font-weight: bold;
    margin-right: 1rem;
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "endMessage": "Beginning of mass message history",
    "error": {
      "load": "Unable to load transactions",
      "preview": "Failed to Load Preview"
    },
    "table": {
      "label": {
        "id": "ID",
        "date": "Date",
        "gross": "Gross",
        "fees": "Fees",
        "net": "Net",
        "type": "Trans Type",
        "itemType": "Item Type",
        "view": "View Item",
        "purchaser": "Purchaser"
      }
    }
  }
}
</i18n>
