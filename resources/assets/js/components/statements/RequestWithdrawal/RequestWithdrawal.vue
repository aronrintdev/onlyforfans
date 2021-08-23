<template>
  <div>
    <b-btn variant="primary" :disabled="disabled" @click="onOpen">
      {{ $t('button') }}
    </b-btn>
    <b-modal v-model="modalOpen" size="lg" :title="$t('modal.title')" hide-footer>

      <div v-if="$isEmpty(accounts)">
        <NoAccounts />
      </div>

      <div v-if="!$isEmpty(accounts)">
        <AccountSelect :accounts="accounts" :selected="selectedAccount" @selected="onSelectAccount" />

        <b-collapse :visible="selectedAccount !== ''">
          <div class="d-flex flex-column w-100 mt-3">
            <PriceSelector
              v-model="requestAmount"
              :min="0"
              :max="parseInt(balance.amount)"
              :currency="balance.currency"
              :label="$t('selectorLabel')"
              :limitWidth="false"
              class="flex-grow-1"
            >
              <template #append>
                <b-btn variant="link" @click="setToBalance">
                  <fa-layers full-width class="fa-lg">
                    <fa-icon icon="long-arrow-alt-up" transform="left-5 shrink-2 up-1" />
                    <fa-icon icon="dollar-sign" transform="right-5" />
                  </fa-layers>
                </b-btn>
              </template>
            </PriceSelector>
            <div class="ml-auto d-flex">
              <b-btn variant="secondary" class="mr-3" @click="modalOpen = false">
                {{ $t('cancel') }}
              </b-btn>
              <b-btn
                :disabled="processing"
                variant="primary"
                class="d-flex align-content-center"
                @click="submit"
              >
                {{ $t('payoutButton') }}
                <fa-icon v-if="processing" icon="spinner" spin size="lg" fixed-width class="ml-3" />
                <fa-layers v-else full-width class="fa-lg ml-3">
                  <fa-icon icon="caret-right" transform="right-6" />
                  <fa-icon icon="dollar-sign" transform="left-6" />
                </fa-layers>
              </b-btn>
            </div>
          </div>

        </b-collapse>
      </div>
      <transition name="quick-fade">
        <ConfirmationCheckAnime v-if="showCompleted" />
      </transition>
    </b-modal>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/RequestWithdrawal/RequestWithdrawal.vue
 *
 * Withdrawal request modal
 */
import { eventBus } from '@/eventBus'
import _ from 'lodash'
import Vuex from 'vuex'
import AccountSelect from './AccountSelect'
import NoAccounts from './NoAccounts'
import PriceSelector from '@components/common/PriceSelector'
import ConfirmationCheckAnime from '@components/common/flair/ConfirmationCheckAnime'

export default {
  name: 'RequestWithdrawal',

  components: {
    AccountSelect,
    ConfirmationCheckAnime,
    NoAccounts,
    PriceSelector,
  },

  props: {
    balance: { type: Object, default: () => ({ amount: 0, currency: 'USD' }) },
    /** If the request withdrawal is disabled */
    disabled: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState('banking', [ 'accounts' ]),
  },

  data: () => ({
    modalOpen: false,
    selectedAccount: '',
    processing: false,
    requestAmount: 0,

    animationLength: 2000,
    showCompleted: false,
  }),

  methods: {
    ...Vuex.mapActions('banking', [ 'updateAccounts' ]),

    completeAnimate() {
      this.showCompleted = true
      setTimeout(() => {
        this.modalOpen = false
        this.showCompleted = false
        this.$emit('completed')
      }, this.animationLength)
    },

    onOpen() {
      if (this.accounts) {
        const index = _.findIndex(this.accounts, 'default')
        this.selectedAccount = index === -1 ? '' : this.accounts[index].id
      }
      this.setToBalance()
      this.modalOpen = true
    },

    onSelectAccount(account) {
      if (typeof account === 'object') {
        this.selectedAccount = account.id
      } else {
        this.selectedAccount - account
      }
    },

    setToBalance() {
      this.requestAmount = parseInt(this.balance.amount)
    },

    submit() {
      this.processing = true
      // Send withdrawal request
      this.axios.post(this.$apiRoute('payouts.request'), {
        ach_id: this.selectedAccount,
        amount: this.requestAmount,
        currency: this.balance.currency
      }).then(response => {
        this.processing = false;
        this.completeAnimate()
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('processingError') })
        this.processing = false;
      })
    },

  },

  watch: {},

  created() {
    this.updateAccounts()
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "button": "Request Withdrawal",
    "cancel": "Cancel",
    "modal": {
      "title": "Request Withdrawal"
    },
    "selectorLabel": "Withdrawal Amount",
    "payoutButton": "Request Payout",
    "processingError": "There was an issue requesting this payout. Please try again later"
  }
}
</i18n>
