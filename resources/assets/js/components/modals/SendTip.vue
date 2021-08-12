<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <router-link :to="tippedTimelineUrl">
          <b-img-lazy :src="tippedTimeline.avatar.filepath" :alt="tippedTimeline.name" :title="tippedTimeline.name" />
        </router-link>
      </section>
      <section class="user-details">
        <div>
          <router-link :to="tippedTimelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">
            {{ tippedTimeline.name }}
          </router-link>
          <span v-if="tippedTimeline.verified" class="verified-badge">
            <fa-icon icon="check-circle" class="text-primary" />
          </span>
        </div>
        <div>
          <span class="text-secondary">@{{ tippedTimeline.slug }}</span>
        </div>
      </section>
    </b-card-header>

    <PaymentsDisabled v-if="paymentsDisabled" />

    <transition name="quick-fade" mode="out-in">
      <b-form v-if="step === 'initial'" @submit="sendTip">
        <b-card-body>
          <b-button variant="light" @click="formPayload.amount = 500">$5.00</b-button>
          <b-button variant="light" @click="formPayload.amount = 1000">$10.00</b-button>
          <b-button variant="light" @click="formPayload.amount = 2000">$20.00</b-button>
          <b-button variant="light" @click="formPayload.amount = 5000">$50.00</b-button>
          <b-button variant="light" @click="showCustomPrice = true">Other</b-button>

          <b-form-spinbutton
            v-if="showCustomPrice"
            id="tip-amount"
            class="w-100 mx-auto tag-tip_amount mt-3"
            v-model="formPayload.amount"
            :formatter-fn="$options.filters.niceCurrency"
            :min="config.min"
            :max="config.max"
            :step="config.step"
          />

          <textarea
            v-model="formPayload.message"
            cols="60"
            rows="5"
            class="w-100 tip-modal-text"
            placeholder="Write a message"
          ></textarea>

        </b-card-body>

        <b-card-footer>
          <b-btn type="submit" :disabled="paymentsDisabled" variant="primary" class="w-100">Send Tip</b-btn>
        </b-card-footer>
      </b-form>

      <b-card-body v-if="step === 'payment'">
        <PurchaseForm
          :value="payload.resource"
          :item-type="payload.resource_type"
          :price="formPayload.amount"
          :currency="formPayload.currency"
          type="tip"
          :display-price="formPayload.amount | niceCurrency"
          :extra="{ message: formPayload.message }"
          class="mt-3"
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
import PurchaseForm from '@components/payments/PurchaseForm'

import PaymentsDisabled from '@components/payments/PaymentsDisabled'

// Tip timeline on another user's timeline page / feed
// Tip post on another user's timeline page / feed
// Tip post on one's own home page / feed
export default {
  name: 'SendTip',

  components: {
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
      return route('timelines.show', this.tippedTimeline.slug)
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

    showCustomPrice: false,
  }),

  created() {
    if ( paymentsDisabled || window.paymentsDisabled ) {
      this.paymentsDisabled = true
    }
  },

  methods: {

    sendTip(e) {
      e.preventDefault()
      this.step = 'payment'
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


body .user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.tip-modal-text {
  border: solid 1px #dfdfdf;
  margin-top: 1rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
