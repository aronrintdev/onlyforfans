<template>
  <div>
    <b-pagination
      v-if="mobile"
      v-model="page"
      :total-rows="totalTransactions"
      :per-page="take"
      aria-controls="earnings-transactions-table"
    />

    <div v-if="mobile">
      <transition-group name="quick-fade" mode="out-in">
        <TransactionCard
          v-for="item in transactions"
          :key="item.id"
          :value="item"
          :fields="fieldsObj"
          class="mb-3"
          @preview="preview"
        />
      </transition-group>

    </div>
    <b-table
      v-else
      hover
      responsive
      id="earnings-transactions-table"
      :items="transactions"
      :fields="fields"
      :busy="transactionsLoading"
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
import TransactionCard from './TransactionCard'

export default {
  name: 'TransactionsTable',

  components: {
    LoadingOverlay,
    PostDisplay,
    TransactionCard,
    UserAvatar,
  },

  computed: {
    ...Vuex.mapState(['session_user', 'mobile', 'screenSize']),



    encoder() {
      return this.$newEncoder(this.encodeBase)
    },

    fields() {
      return [{
          key: 'id',
          label: this.$t('table.label.id'),
          formatter: (value, key, item) => {
            if (this.spliceId) {
              return this.encoder.encode(value.slice(15))
            }
            return this.encoder.encode(value)
          }
        }, {
          key: 'created_at',
          label: this.$t('table.label.date'),
          formatter: (value, key, item) => {
            // return value
            return moment(value).format('MMMM Do, YYYY')
          }
        }, {
          key: 'credit_amount',
          label: this.$t('table.label.gross'),
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(value)
          }
        }, {
          key: 'fees',
          label: this.$t('table.label.fees'),
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(
              _.sum(_.flatMap(value, v => parseInt(v)))
            )
          }
        }, {
          key: 'net_amount',
          label: this.$t('table.label.net'),
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(
              parseInt(item.credit_amount) - _.sum(_.flatMap(item.fees, v => parseInt(v)))
            )
          }
        }, {
          key: 'type',
          label: this.$t('table.label.type')
        }, {
          key: 'purchasable_type',
          label: this.$t('table.label.itemType'),
        }, {
          key: 'purchasable',
          label: this.$t('table.label.view'),
        }, {
          key: 'purchaser',
          label: this.$t('table.label.purchaser'),
        },
      ]
    },

    fieldsObj() {
      return _.keyBy(this.fields, 'key')
    }
  },

  data: () => ({
    // Pagination
    page: 1,
    take: 10,
    totalPages: 1,
    totalTransactions: 0,
    transactionsLoading: false,

    transactions: [],

    // Preview
    previewOpen: false,
    previewLoading: false,
    previewItem: null,

    // Options
    spliceId: true,
    encodeBase: 'base58',
  }),

  methods: {
    ...Vuex.mapActions('posts', [ 'getPost' ]),

    load() {
      this.transactionsLoading = true
      this.axios.get(this.$apiRoute('earnings.transactions'), { params: { take: this.take, page: this.page } })
        .then(response => {
          this.transactions = response.data.data
          this.totalPages = response.data.meta.last_page
          this.totalTransactions = response.data.meta.total
          this.transactionsLoading = false
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('error.load') })
          this.transactionsLoading = false
        })
    },
    preview(item) {
      if (item.purchasable_type === 'posts') {
        this.previewOpen = true
        this.previewLoading = true
        this.getPost(item.purchasable_id)
          .then(post => {
            this.previewItem = post
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
