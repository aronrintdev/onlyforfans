<template>
  <b-card no-body>

    <b-card-header>
      <AvatarWithStatus :timeline="tippedTimeline" :user="tippedTimeline.user" :textVisible="true" size="md" />
    </b-card-header>

    <PaymentsDisabled v-if="paymentsDisabled" />

    <transition name="quick-fade" mode="out-in">
      <b-form v-if="step === 'initial'" @submit="sendTip">
        <b-card-body class="pt-2">
          <TipInput ref="input" v-model="tip" @isValid="onIsValid" />
          <textarea
            v-model="message"
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
          :price="tip.amount"
          :currency="tip.currency"
          type="tip"
          :display-price="tip.amount | niceCurrency"
          :extra="{ message: message }"
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
import PurchaseForm from '@components/payments/PurchaseForm'
import PaymentsDisabled from '@components/payments/PaymentsDisabled'
import AvatarWithStatus from '@components/user/AvatarWithStatus'
import TipInput from '@components/forms/elements/TipInput'

// Tip timeline on another user's timeline page / feed
// Tip post on another user's timeline page / feed
// Tip post on one's own home page / feed
export default {
  name: 'SendTip',

  components: {
    AvatarWithStatus,
    PaymentsDisabled,
    PurchaseForm,
    TipInput,
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

    defaultAmount: 500, // $5.00

    tip: { amount: 0, currency: 'USD' },
    message: '',
    isValid: true,

    finished: false,
  }),

  methods: {

    onCompleted(tip) {
      this.$log.debug('SendTip onCompleted', { tip })
      if (this.$refs['input'].$refs['input'].validate() === true) {
        // false === 'invalid' null === 'valid'
        if (this.isValid !== false) {
          if (this.tip.amount === 0) {
            this.tip.amount = this.defaultAmount
          }
          this.$emit('submit', this.tip)
          this.onHide()
        }
      }
      if (typeof this.payload.callback === 'function') {
        this.finished = true
        this.payload.callback(tip)
      }
    },

    onIsValid(value) {
      this.isValid = value
    },

    sendTip(e) {
      e.preventDefault()
      this.step = 'payment'
    },

    getVariant(amount) {
      if (amount === this.tip.amount && !this.showCustomPrice) return 'secondary'
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
      this.tip.amount = this.payload.tip.amount
      this.tip.currency = this.payload.tip.currency
    }
    if (this.payload.skipMessage) {
      this.step = 'payment'
    }
    if (this.payload.message) {
      this.message = this.payload.message
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
    "label": "Tip Amount",
    "send": "Send Tip"
  }
}
</i18n>
