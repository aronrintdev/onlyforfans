<template>
  <b-card no-body>
    <b-card-header v-if="timeline">
      <AvatarWithStatus :timeline="timeline" :user="timeline.user" :textVisible="true" size="md" />
    </b-card-header>
    <transition name="quick-fade" mode="out-in">
      <b-card-body v-if="step === 'initial'" key="initial">
        <div>
          <b-row>
            <b-col class="mb-2">
              <p v-if="!subscribe_only"><fa-icon :icon="['fas', 'check']" /> Get access to purchase content</p>
              <p v-else><fa-icon :icon="['fas', 'check']" /> Get access to premium content</p>
              <p v-if="!subscribe_only"><fa-icon :icon="['fas', 'check']" /> Upgrade to full access anytime</p>
              <!-- <p v-if="subscribe_only"><fa-icon :icon="['fas', 'check']" /> Full access for {{ timeline.userstats.subscriptions.price_per_1_months * 100 | niceCurrency }} monthly</p> -->
              <p><fa-icon :icon="['fas', 'check']" /> Quick and easy cancellation</p>
              <p><fa-icon :icon="['fas', 'check']" /> Safe and secure transaction</p>
              <p><fa-icon :icon="['fas', 'check']" /> Ability to Message with {{ timeline.name }}</p>
            </b-col>
          </b-row>

          <b-button v-if="subscribe_only" @click="doSubscribe" :disabled="isInProcess" variant="primary" class="w-100">
            <b-spinner small v-if="isInProcess" class="mr-2"></b-spinner>
            <template v-if="timeline.userstats.is_sub_discounted">
              Subscribe for {{ timeline.userstats.display_prices_in_cents.subscribe_1_month_discounted | niceCurrency }} now
            </template>
            <template v-else>
              Subscribe for {{ timeline.userstats.display_prices_in_cents.subscribe_1_month | niceCurrency }} per month now
            </template>
          </b-button>
          <p v-if="subscribe_only && timeline.userstats.is_sub_discounted" class="text-muted text-center m-0"><small>
            Discount applied: Subscribe for {{ timeline.userstats.display_prices_in_cents.subscribe_1_month_discounted | niceCurrency}}. 
            Renews at {{  timeline.userstats.display_prices_in_cents.subscribe_1_month | niceCurrency  }}
          </small></p>

          <b-button v-if="!subscribe_only" @click="doFollow" :disabled="isInProcess" variant="primary" class="w-100 mt-3">
            <b-spinner small v-if="isInProcess" class="mr-2"></b-spinner>
            Follow Now
          </b-button>

        </div>
      </b-card-body>

      <b-card-body v-if="step === 'payment'" key="payment">
        <PaymentsDisabled class="mx-4 mt-4 mb-2" v-if="paymentsDisabled" />
        <PurchaseForm
          v-else
          :value="timeline"
          item-type="timeline"
          :price="price"
          :currency="'USD'"
          type="subscription"
          :display-price="price | niceCurrency"
          class="mt-3"
        />
      </b-card-body>
    </transition>
  </b-card>
</template>

<script>
import { eventBus } from '@/eventBus'
import AvatarWithStatus from '@components/user/AvatarWithStatus'
import PurchaseForm from '@components/payments/PurchaseForm'
import PaymentsDisabled from '@components/payments/PaymentsDisabled'

export default {

  components: {
    AvatarWithStatus,
    PurchaseForm,
    PaymentsDisabled,
  },

  props: {
    session_user: null,
    timeline: null,
    subscribe_only: { type: Boolean, default: true },
  },

  computed: {
    timelineUrl() {
      return `/${this.timeline.slug}`
    },

    price() {
      if (this.timeline.userstats.is_sub_discounted) {
        return this.timeline.userstats.display_prices_in_cents.subscribe_1_month_discounted
      }
      return this.timeline.userstats.display_prices_in_cents.subscribe_1_month
    }
  },

  created() {
    try {
      if ( window.paymentsDisabled || paymentsDisabled ) {
        this.paymentsDisabled = true
      }
    } catch (e) {}
  },

  mounted() {
    console.log({timeline: this.timeline})
    if (true || this.subscribe_only) {
      this.getUserCampaign() // if none exists will return null
    }
  },

  data: () => ({
    /** 'initial' | 'payment' */
    step: 'initial',
    userCampaign: null,
    isInProcess: false,
    paymentsDisabled: false,
  }),

  methods: {

    async doFollow(e) {
      this.isInProcess = true
      e.preventDefault()
      const response = await this.axios.put( this.$apiRoute('timelines.follow', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow')
      const msg = response.data.is_following
        ? `You are now following ${this.timeline.name}!`
        : `You are no longer following ${this.timeline.name}!`
      this.$root.$bvToast.toast(msg, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
        variant: 'success',
      })
      this.isInProcess = false
      // %FIXME: this emit should be more general, as this modal may be used elsewhere
       eventBus.$emit('update-timelines', this.timeline.id)
    },

    async doSubscribe(e) {
      e.preventDefault()
      this.step = 'payment'

      // if (!this.paymentsDisabled) {
      //   this.isInProcess = true
      //   const response = await this.axios.put( this.$apiRoute('timelines.subscribe', this.timeline.id), {
      //     account_id: this.session_user.id,
      //     amount: this.price,
      //     currency: 'USD',
      //     // currency: this.timeline.currency,
      //   })
      //   this.$bvModal.hide('modal-follow')
      //   this.isInProcess = false

      //   // Needs to be moved

      //   const msg = response.is_subscribed
      //     ? `You are now subscribed to ${this.timeline.name}!`
      //     : `You are no longer subscribed to ${this.timeline.name}!`
      //   this.$root.$bvToast.toast(msg, {
      //     toaster: 'b-toaster-top-center',
      //     title: 'Success!',
      //   })
      //   eventBus.$emit('update-originator') // %ERIK use this
      //   eventBus.$emit('update-timeline', this.timeline.id)
      //   eventBus.$emit('update-feed') // updates feed being viewed
      // }
    },

    async getUserCampaign() {
      const response = await this.axios.get( this.$apiRoute('campaigns.showActive', this.timeline.user.id) )
      this.userCampaign = response.data.data
    },

  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}

/* %TODO DRY */
body .user-avatar {
  width: 80px;
  height: 80px;
  float: left;
  margin-right: 10px;
  position: relative;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid #fff;
}
.user-avatar .onlineStatus {
  position: absolute;
  bottom: 5px;
  right: -5px;
}
body .user-details {
  margin-top: 4px;
}
body .user-details .tag-username {
  color: #859AB5;
}
</style>
