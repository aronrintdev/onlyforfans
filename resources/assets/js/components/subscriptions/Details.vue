<template>
  <div>
    <div class="mb-3">
      <b-btn variant="secondary" @click="onBack">
        <fa-icon icon="caret-left" fixed-width /> <span v-text="$t('nav.prev')" />
      </b-btn>
    </div>
    <b-skeleton-wrapper :loading="loading || !subscription">
      <template #loading></template>
      <div v-if="subscription !== null">
        <div>
          Your subscription to {{ subscription.for.name }}
        </div>

        <div class="controls">
          <b-card
            v-if="false"
            :title="$t('payment.title')"
            class="my-3"
          >
            <SavedPaymentMethod :value="subscription.payment_method" :selectable="false">
              <b-btn variant="info" class="ml-auto">
                {{ $t('payment.update.button') }}
              </b-btn>
            </SavedPaymentMethod>
          </b-card>

          <b-btn
            v-if="subscription.active && !subscription.canceled"
            variant="danger"
            class="my-3"
            @click="cancelConfirmation = true"
          >
            <fa-icon icon="times" fixed-width />
            {{ $t('cancel.button') }}
          </b-btn>
          <div v-if="subscription.canceled" class="text-danger" v-text="$t('cancel.isCanceled')" />
        </div>

        <!-- <div class="h3" v-text="$t('transactions.title')" /> -->
        <!-- <hr /> -->
        <!-- Transaction Table Here -->
        <!-- <div class="text-center">WIP</div> -->

        <b-modal
          v-model="cancelConfirmation"
          header-bg-variant="danger"
          header-text-variant="light"
          :title="$t('cancel.confirm.title')"
          ok-variant="danger"
          @ok="onCancel"
          :cancel-title="$t('cancel.confirm.cancel')"
        >
          {{ $t('cancel.confirm.body', { name: subscription.for.name }) }}
          <template #modal-ok>
            <fa-icon icon="times" fixed-width />
            {{ $t('cancel.confirm.ok') }}
          </template>
        </b-modal>
      </div>
    </b-skeleton-wrapper>
  </div>
</template>

<script>
/**
 * Details of a subscription
 */
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import SavedPaymentMethod from '@components/payments/SavedPaymentMethod'

export default {
  name: 'SubscriptionDetails',

  components: {
    SavedPaymentMethod,
  },

  props: {
    id: { type: String, default: '' },
  },

  computed: {
    ...Vuex.mapState('subscriptions', [ 'active', 'inactive' ]),

    decodedId() {
      if (this.$checkUuid(this.id)) {
        return this.id
      }
      return this.$encoder.decode(this.id)
    },

    subscription() {
      return this.getSubscriptionById()(this.decodedId)
    },
  },

  data: () => ({
    loading: false,
    cancelConfirmation: false,
  }),

  methods: {
    ...Vuex.mapActions('subscriptions', [ 'getSubscription', 'cancelSubscription' ]),
    ...Vuex.mapGetters('subscriptions', [ 'getSubscriptionById' ]),

    init() {
      if (this.subscription === null) {
        this.loading = true
        this.getSubscription(this.decodedId).then(() => this.loading = false)
          .catch(error => eventBus.$emit('error', { error, message: this.$t('error.msg') }))
      }
    },

    loadTransactions() {
      // TODO: Hook up subscription transactions
    },

    onBack() {
      this.$router.go(-1)
    },

    onCancel() {
      this.cancelSubscription(this.subscription)
    },

    onChangePaymentMethod() {
      // TODO: Hook up change payment method
    },
  },

  mounted() {
    this.init()
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "error": {
      "msg": "An error occurred while loading this subscription"
    },
    "nav": {
      "prev": "Back"
    },
    "cancel": {
      "button": "Cancel Subscription",
      "confirm": {
        "title": "Are You Sure?",
        "body": "Are you sure you wish to cancel you subscription to {name}",
        "ok": "Yes, Cancel Subscription",
        "cancel": "Cancel"
      },
      "isCanceled": "This subscription has been canceled."
    },
    "payment": {
      "title": "Payment Method",
      "update": {
        "button": "Change Payment Method"
      }
    },
    "transactions": {
      "title": "Transactions"
    }
  }
}
</i18n>
