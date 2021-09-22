<template>
  <div v-if="!isLoading" class="container-fluid" id="component-stats_dashboard">

    <section>

      <b-card class="pt-3 px-sm-3" no-body>

        <div class="d-flex align-items-center">
          <b-btn variant="link" class="" @click="onBackClicked">
            <fa-icon :icon="['fas', 'arrow-left']" class="fa-lg" />
          </b-btn>
          <h4 class="m-0">Mass Message Stats</h4>
        </div>

        <section class="crate-filters mb-3 d-flex">
          <!-- filters -->
            <!--
          <div class="box-filter p-3">
            <h6>Boolean</h6>
            <b-button v-for="(f,idx) in postFilters.booleans" :key="idx" @click="toggleFilter('booleans', f)" :variant="f.is_active ? 'primary' : 'outline-primary'" class="mr-3">{{ f.label }}</b-button>
          </div>
          -->
        </section>

        <section class="crate-pagination d-sm-flex flex-row-reverse align-items-center mb-3 px-2 px-sm-0">
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
          sort-icon-left
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

          <template #cell(unsend_action)="data">
            <span v-if="data.item.sent_count !== data.item.read_count" class="text-info clickable" @click="showConfirmModal(data.item.id)">Unsend</span>
          </template>
        </b-table>
        
        <section v-if="!mobile" class="crate-pagination d-flex align-items-center my-3">
          <b-pagination v-model="currentPage" :total-rows="totalItems" :per-page="itemsPerPage" class="m-0" aria-controls="earnings-transactions-table" />
          <div class="ml-3">({{ totalItems }})</div>
        </section>

        <section v-if="mobile" class="crate-as_cards"> <!-- responsive 'table' using cards -->
          <b-card v-for="(cmg,idx) in chatmessagegroups" :key="idx" v-observe-visibility="idx===chatmessagegroups.length-1 ? visibilityChanged : false" class="px-1 py-2 mb-3" no-body>
            <b-list-group flush>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Date</div> <div class="ml-auto">{{ cmg.created_at }}</div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Text</div> <div class="ml-auto" style="width: 80%;"><ContentViewer :str=cmg.mcontent /></div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Attachment</div>
                <div class="ml-auto">
                  <span v-if="cmg.attachment_counts.images_count" class="ml-0"><fa-icon :icon="['far', 'image']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.images_count }}</span>
                  <span v-if="cmg.attachment_counts.videos_count" class="ml-2"><fa-icon :icon="['far', 'video']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.videos_count }}</span>
                  <span v-if="cmg.attachment_counts.audios_count" class="ml-2"><fa-icon :icon="['far', 'microphone']" class="fa-sm" fixed-width /> {{ cmg.attachment_counts.audios_count }}</span>
                </div>
              </b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Price</div> <div class="ml-auto">{{ cmg.price | niceCurrency }}</div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Sent</div> <div class="ml-auto">{{ cmg.sent_count }}</div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Viewed</div> <div class="ml-auto">{{ cmg.read_count }}</div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Purchased</div> <div class="ml-auto">{{ cmg.purchased_count }}</div></b-list-group-item>
              <b-list-group-item class="d-flex"><div class="text-muted tag-label">Unsend</div> <div v-if="cmg.sent_count !== cmg.read_count" class="ml-auto clickable text-info" @click="showConfirmModal(cmg.id)">Unsend</div></b-list-group-item>
            </b-list-group>
          </b-card>

          <div v-if="!isScrollingViewFullyLoaded" class="d-flex justify-content-center align-content-center">
            <b-button @click="loadMore" variant="primary" class="w-100" :disabled="isScrollingViewFullyLoaded||isDataLoading">
              <fa-icon v-if="isDataLoading" icon="spinner" size="2x" fixed-width spin />
              <div v-else>Load More</div>
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

    <b-modal id="confirm-undsend" v-model="isConfirmModalVisible" :title="$t('unsend.title')" :centered="mobile">
      <div v-text="$t('unsend.description')" />
      <template #modal-footer>
        <b-button variant="primary" @click="onUnsendClicked">Confirm</b-button>
        <b-button @click="hideConfirmModal">Cancel</b-button>
      </template>
    </b-modal>

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
        { key: 'unsend_action', label: 'Unsend' },
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
    sortDesc: true,
    chatmessagegroups: [],
    iisDataLoading: false,

    filters: {
      qsearch: null, // search query string
    },

    selectedId: null,
    isConfirmModalVisible: false,
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
    },

    onBackClicked() {
      this.$router.push({name: 'chatthreads.dashboard'})
    },

    onUnsendClicked() {
      this.isConfirmModalVisible = false
      axios.post(route('chatmessagegroups.unsend', { id: this.selectedId })).then(() => {
        const index = this.chatmessagegroups.findIndex(cm => cm.id === this.selectedId)
        this.chatmessagegroups[index].sent_count = this.chatmessagegroups[index].read_count
        this.$forceUpdate()
      })
    },

    showConfirmModal(id) {
      this.selectedId = id
      this.isConfirmModalVisible = true
    },

    hideConfirmModal() {
      this.isConfirmModalVisible = false
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
#component-stats_dashboard {
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
      //font-weight: bold;
      margin-right: 2rem;
    }
  }
  ::v-deep .crate-as_cards .list-group .list-group-item {
    border: none !important;
    padding: 0 0.50rem;
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
    },
    "unsend": {
      "title": "Unsend this message",
      "description": "Unsend this message for everyone who has not purchased it yet"
    }
  }
}
</i18n>
