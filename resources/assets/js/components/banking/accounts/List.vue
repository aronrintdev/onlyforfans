<template>
  <div>
    <b-list-group class="bank-account-list">
      <ListItem v-for="item in accounts" :key="item.id" :value="item" />
    </b-list-group>
    <div class="mt-3">
      <b-btn variant="primary" block :to="{ name: 'banking.accounts.new' }">
        <fa-icon icon="plus" />
        {{ $t('add') }}
      </b-btn>
    </div>

  </div>
</template>

<script>
/**
 * js/components/banking/accounts/List
 */
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import ListItem from './ListItem'

export default {
  name: 'BankingAccountList',

  components: {
    ListItem,
  },

  computed: {
    ...Vuex.mapState('banking', [ 'accounts' ]),
  },

  data: () => ({
    loading: false,
    page: 1,
    take: 20,
  }),

  methods: {
    ...Vuex.mapActions('banking', [ 'updateAccounts' ]),
    init() {
      this.loading = true
      this.updateAccounts({ page: this.page, take: this.take })
        .then(res => { this.loading = false })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('errors.loading') })
          this.loading = false
        })
    }
  },

  created() {
    this.init()
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "add": "Add a new account",
    "errors": {
      "loading": "An Error has occurred while loading your bank accounts"
    }
  }
}
</i18n>
