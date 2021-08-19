<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <router-link :to="timelineUrl"><b-img-lazy :src="timeline.user.avatar.filepath" :alt="timeline.name" :title="timeline.name"></b-img-lazy></router-link>
        <OnlineStatus :user="timeline.user" size="lg" :textInvisible="false" />
      </section>
      <section class="user-details">
        <div>
          <router-link :to="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ timeline.name }}</router-link>
          <span v-if="timeline.verified" class="verified-badge">
            <fa-icon icon="check-circle" class="text-primary" />
          </span>
        </div>
        <div>
          <router-link :to="timelineUrl" class="tag-username">@{{ timeline.slug }}</router-link>
        </div>
      </section>
    </b-card-header>
    <transition name="quick-fade" mode="out-in">
      <b-card-body>
        <div>
          <b-row>
            <b-col class="mb-2">
              <p v-if="!subscribe_only"><fa-icon :icon="['fas', 'check']" /> Get access to purchase content</p>
              <p v-else><fa-icon :icon="['fas', 'check']" /> Get access to premium content</p>
              <p v-if="!subscribe_only"><fa-icon :icon="['fas', 'check']" /> Upgrade to full access anytime</p>
              <p v-if="subscribe_only"><fa-icon :icon="['fas', 'check']" /> Full access for {{ timeline.userstats.subscriptions.price_per_1_months * 100 | niceCurrency }} monthly</p>
              <p><fa-icon :icon="['fas', 'check']" /> Quick and easy cancellation</p>
              <p><fa-icon :icon="['fas', 'check']" /> Safe and secure transaction</p>
              <p><fa-icon :icon="['fas', 'check']" /> Ability to Message with {{ timeline.name }}</p>
              <!-- <h5 v-if="userCampaign.type === 'trial'">Limited offer - Free trial for {{ userCampaign.trial_days }} days!</h5>
              <h5 v-if="userCampaign.type === 'discount'">Limited offer - {{ userCampaign.discount_percent }} % off for 31 days!</h5> -->
              <!-- <p><small class="text-muted">For {{ campaignAudience }} • ends {{ campaignExpDate }} • left {{ userCampaign.subscriber_count }}</small></p> -->
            </b-col>
          </b-row>
          <b-button v-if="subscribe_only" @click="doSubscribe" :disabled="isInProcess" variant="primary" class="w-100 mb-3">
            <b-spinner small v-if="isInProcess" class="mr-2"></b-spinner>
            Subscribe for {{ timeline.userstats.subscriptions.price_per_1_months * 100 | niceCurrency }} per month now
          </b-button>
          <b-button v-if="!subscribe_only" @click="doFollow" :disabled="isInProcess" variant="primary" class="w-100 mb-3">
            <b-spinner small v-if="isInProcess" class="mr-2"></b-spinner>
            Follow Now
          </b-button>
        </div>
      </b-card-body>
    </transition>
  </b-card>
</template>

<script>
import moment from 'moment'
import { eventBus } from '@/eventBus'
import PurchaseForm from '@components/payments/PurchaseForm'
import OnlineStatus from '@components/common/OnlineStatus'

export default {

  components: {
    PurchaseForm,
    OnlineStatus,
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

    campaignAudience() {
      if (this.userCampaign) {
        const { has_new: hasNew, has_expired: hasExpired } = this.userCampaign

        if (hasNew && hasExpired) {
          return 'new & expired subscribers'
        }

        if (hasNew) {
          return 'new subscribers'
        }

        if (hasExpired) {
          return 'expired subscribers'
        }
      }

      return null
    },

    campaignExpDate() {
      if (this.userCampaign) {
        const { created_at: createdAt, offer_days: offerDays } = this.userCampaign
        const startDate = moment(createdAt)
        const expDate = startDate.add(offerDays, 'days')
        return expDate.format('MMM D')
      }

      return null
    }
  },

  created() {
    this.getUserCampaign()
  },

  mounted() {
    console.log({timeline: this.timeline})
  },

  data: () => ({
    /** 'initial' | 'payment' */
    step: 'initial',
    userCampaign: null,
    isInProcess: false,
  }),

  methods: {

    async doFollow(e) {
      this.isInProcess = true
      e.preventDefault()
      const response = await this.axios.put( route('timelines.follow', this.timeline.id), {
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
      this.$bvModal.hide('modal-follow')

      // const response = await this.axios.put( route('timelines.subscribe', this.timeline.id), {
      //   sharee_id: this.session_user.id,
      //   notes: '',
      // })
      // this.$bvModal.hide('modal-follow')


      // Needs to be moved

      // const msg = response.is_subscribed 
      //   ? `You are now subscribed to ${this.timeline.name}!`
      //   : `You are no longer subscribed to ${this.timeline.name}!`
      // this.$root.$bvToast.toast(msg, {
      //   toaster: 'b-toaster-top-center',
      //   title: 'Success!',
      // })
      // eventBus.$emit('update-originator') // %ERIK use this
      // eventBus.$emit('update-timeline', this.timeline.id)
      // eventBus.$emit('update-feed') // updates feed being viewed
    },

    async getUserCampaign() {
      const response = await this.axios.get(route('campaigns.showActive', this.timeline.user.id))
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
