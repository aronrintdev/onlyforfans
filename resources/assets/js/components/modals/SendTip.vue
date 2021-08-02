<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <router-link :to="tippedTimelineUrl">
          <b-img :src="tippedTimeline.avatar.filepath" :alt="tippedTimeline.name" :title="tippedTimeline.name" />
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
          <router-link :to="tippedTimelineUrl" class="tag-username">@{{ tippedTimeline.slug }}</router-link>
        </div>
      </section>
    </b-card-header>

    <PaymentsDisabled v-if="paymentsDisabled" />

    <transition name="quick-fade" mode="out-in">
      <b-form v-if="step === 'initial'" @submit="sendTip">
        <b-card-body>
          <b-form-spinbutton
            id="tip-amount"
            class="w-100 mx-auto tag-tip_amount"
            v-model="formPayload.amount"
            :formatter-fn="$options.filters.niceCurrency"
            min="500"
            max="10000"
            :step="LEDGER_CONFIG.TIP_STEP_DELTA"
          />

          <p class="text-center"><small><span v-if="renderDetails">{{ renderDetails }}</span></small></p>

          <textarea
            v-model="formPayload.message"
            cols="60"
            rows="5"
            class="w-100"
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
import LEDGER_CONFIG from "@/components/constants"
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
    paymentsDisabled: true,

    /** 'initial' | 'payment' */
    step: 'initial',
    LEDGER_CONFIG,
    formPayload: {
      amount: LEDGER_CONFIG.MIN_TIP_IN_CENTS,
      currency: 'USD',
      message: '',
    },
  }),

  created() {
    if ( paymentsDisabled ) {
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

body .user-details .tag-username {
  color: #859AB5;
  text-transform: capitalize;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
