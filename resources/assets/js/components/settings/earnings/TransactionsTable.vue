<template>
  <div>
    <b-table
      hover
      id="earnings-transactions-table"
      :items="transactions"
      :fields="fields"
      :current-page="page"
    >
      <template #cell(purchasable)="data">
        <b-btn v-if="data.item.purchasable_type === 'posts'" variant="outline-primary" @click="preview(data.item)" >
          <fa-icon icon="external-link-alt" />
        </b-btn>
      </template>
      <template #cell(purchaser)="data">
        <div class="d-flex align-items-center">
          <UserAvatar v-if="data.value" :user="data.value" size="sm" class="mr-2" />
          {{ data.value.username }}
        </div>
      </template>
    </b-table>
    <b-pagination
      v-model="page"
      :total-rows="totalTransactions"
      :per-page="take"
      aria-controls="earnings-transactions-table"
    />
    <b-modal v-model="previewOpen" size="xl" hide-footer>
      <LoadingOverlay v-if="previewLoading" />
      <PostDisplay v-else :post="previewItem" :session_user="session_user" />
    </b-modal>
  </div>
</template>

<script>
import _ from 'lodash'
import Vue from 'vue'
import Vuex from 'vuex'
import { eventBus } from '@/app'
import moment from 'moment'

import LoadingOverlay from '@components/common/LoadingOverlay'
import PostDisplay from '@components/posts/Display'
import UserAvatar from '@components/user/Avatar'

export default {
  name: 'TransactionsTable',

  components: {
    LoadingOverlay,
    PostDisplay,
    UserAvatar,
  },

  computed: {
    ...Vuex.mapState(['session_user']),

    fields() {
      return [{
          key: 'id',
          label: 'ID',
          formatter: (value, key, item) => {
            return this.$encoder.encode(value)
          }
        }, {
          key: 'created_at',
          label: 'Date',
          formatter: (value, key, item) => {
            // return value
            return moment(value).format('MMMM Do, YYYY')
          }
        }, {
          key: 'credit_amount',
          label: 'Gross',
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(value)
          }
        }, {
          key: 'fees',
          label: 'Fees',
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(
              _.sum(_.flatMap(value, v => parseInt(v)))
            )
          }
        }, {
          key: 'net_amount',
          label: 'Net',
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(
              parseInt(item.credit_amount) - _.sum(_.flatMap(item.fees, v => parseInt(v)))
            )
          }
        }, {
          key: 'type',
          label: 'Trans Type'
        }, {
          key: 'purchasable_type',
          label: 'Item Type',
        }, {
          key: 'purchasable',
          label: 'View Item',
        }, {
          key: 'purchaser',
          label: 'Purchaser',
        },
      ]
    },
  },

  data: () => ({
    page: 1,
    previewOpen: false,
    previewLoading: false,
    previewItem: null,
    take: 10,
    totalPages: 1,
    totalTransactions: 0,
    transactions: [],
  }),

  methods: {
    load() {
      this.axios.get(this.$apiRoute('earnings.transactions'), { params: { take: this.take, page: this.page } })
      .then(response => {
        this.transactions = response.data.data
        this.totalPages = response.data.meta.last_page
        this.totalTransactions = response.data.meta.total
      })
      .catch(error => eventBus.$emit('error', { error, message: this.$t('error.load') }))
    },
    preview(item) {
      if (item.purchasable_type === 'posts') {
        this.previewOpen = true
        this.previewLoading = true
        this.axios.get(this.$apiRoute('posts.show', { post: item.purchasable_id }))
        .then(response => {
          this.previewItem = response.data.data
          this.previewLoading = false
        })
        .catch(error => eventBus.$emit('error', { error, message: this.$t('error.preview') }))
      }
    },
  },

  watch: {
    page(value) {
      this.load()
    }
  },

  mounted() {
    this.load()
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "error": {
      "load": "Unable to load transactions",
      "preview": "Failed to Load Preview"
    }
  }
}
</i18n>
