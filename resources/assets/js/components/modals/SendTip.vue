<template>
  <b-card no-body>

    <b-card-header>
      <AvatarWithStatus :timeline="tippedTimeline" :user="tippedTimeline.user" :textVisible="false" size="md" />
    </b-card-header>

    <PaymentsDisabled v-if="paymentsDisabled" />

    <transition name="quick-fade" mode="out-in">
      <b-form v-if="step === 'initial'" @submit="sendTip">
        <b-card-body class="pt-2">
          <PriceSelector
            class="mb-0"
            label=" "
            v-model="formPayload.amount"
            autofocus
          />

          <textarea
            v-model="formPayload.message"
            cols="60"
            rows="5"
            class="w-100 p-2 tip-modal-text"
            placeholder="Write a message"
          ></textarea>

        </b-card-body>

        <b-card-footer>
          <b-btn type="submit" :disabled="paymentsDisabled" variant="primary" class="w-100">
            {{ $t('send') }}
          </b-btn>
        </b-card-footer>
      </b-form>

      <b-card-body v-if="step === 'payment' && !paymentsDisabled">
        <PurchaseForm
          :value="payload.resource"
          :item-type="payload.resource_type"
          :price="formPayload.amount"
          :currency="formPayload.currency"
          type="tip"
          :display-price="formPayload.amount | niceCurrency"
          :extra="{ message: formPayload.message }"
          :callback="payload.callback"
          :wantsMessage="payload.wantsMessage || false"
          @completed="onCompleted"
        />
      </b-card-body>
    </transition>
  </b-card>
</template>

<script>
/**
 * Send Tip Modal Content
 */
import { eventBus } from '@/eventBus'
import PriceSelector from '@components/common/PriceSelector';
import PurchaseForm from '@components/payments/PurchaseForm'
import PaymentsDisabled from '@components/payments/PaymentsDisabled'
import AvatarWithStatus from '@components/user/AvatarWithStatus'

// Tip timeline on another user's timeline page / feed
// Tip post on another user's timeline page / feed
// Tip post on one's own home page / feed
export default {
  name: 'SendTip',

  components: {
    AvatarWithStatus,
    PriceSelector,
    PaymentsDisabled,
    PurchaseForm,
  },

  props: {
    session_user: null,
    payload: null, // JSON object that contains attribute 'resource', the Tippable like 'Post' or 'Timeline' that is the target
  },

  computed: {
    tippedTimeline() {
      switch(this.payload.resource_type)  {
        case 'timelines':
          return this.payload.resource
        case 'posts':
          return this.payload.resource.timeline
      }
    },

    tippedTimelineUrl() {
      return { name: 'timeline.show', params: { slug: this.tippedTimeline.slug } }
    },

    renderDetails() {
      const { resource, resource_type } = this.payload
      switch (resource_type) {
        case 'timelines':
          return 'Send Tip to User'
        case 'posts':
          return 'Send Tip to Post'
        default:
          return null
      }
    },

    avatarImage() {
      if (this.tippedTimeline) {
        const { avatar } = this.tippedTimeline
        return avatar ? avatar.filepath : '/images/default_avatar.png'
      }
      return '/images/default_avatar.png'
    },
  },

  data: () => ({
    paymentsDisabled: false,
    /** 'initial' | 'payment' */
    step: 'initial',

    config: {
      min: 500,   // $  5.00
      max: 10000, // $100.00
      step: 100,  // $  1.00
    },

    formPayload: {
      amount: 500, // $5.00
      currency: 'USD',
      message: '',
    },

    finished: false,
  }),

  methods: {

    onCompleted(tip) {
      this.$log.debug('SendTip onCompleted', { tip })
      if (typeof this.payload.callback === 'function') {
        this.finished = true
        this.payload.callback(tip)
      }
    },

    sendTip(e) {
      e.preventDefault()
      this.step = 'payment'
    },

    getVariant(amount) {
      if (amount === this.formPayload.amount && !this.showCustomPrice) return 'secondary'
      if (!amount && this.showCustomPrice) return 'secondary'
      return 'light'
    }
  },

    created() {
    try {
      if ( window.paymentsDisabled || paymentsDisabled ) {
        this.paymentsDisabled = true
      }
    } catch (e) {}
    if (this.payload.tip) {
      this.formPayload.amount = this.payload.tip.amount
      this.formPayload.currency = this.payload.tip.currency
    }
    if (this.payload.skipMessage) {
      this.step = 'payment'
    }
    if (this.payload.message) {
      this.formPayload.message = this.payload.message
    }
  },

  beforeDestroy() {
    // Handles if modal closes
    this.$log.debug('SendTip Before Destroy Called', { finished: this.finished})
    if (!this.finished && typeof this.payload.callback === 'function') {
      this.payload.callback(false)
    }
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
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
body .user-avatar {
  position: relative;
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}

body .user-avatar .onlineStatus {
  position: absolute;
  top: 20px;
  left: 25px;
}
body .user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}
body .user-details ul > li .username {
  text-transform: capitalize;
}

body .user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
body .user-details ul > li:last-child {
  font-size: 14px;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}

.tip-modal-text {
  border: solid 1px #dfdfdf;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "send": "Send Tip"
  }
}
</i18n>
