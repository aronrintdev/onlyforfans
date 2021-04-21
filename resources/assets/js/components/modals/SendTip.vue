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
            <b-icon icon="check-circle-fill" variant="success" font-scale="1" />
          </span>
        </div>
        <div>
          <router-link :to="tippedTimelineUrl" class="tag-username">@{{ tippedTimeline.slug }}</router-link>
        </div>
      </section>
    </b-card-header>

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
            v-model="formPayload.notes"
            cols="60"
            rows="5"
            class="w-100"
            placeholder="Write a message"
          ></textarea>

        </b-card-body>

        <b-card-footer>
          <b-button type="submit" variant="warning" class="w-100">Send Tip</b-button>
        </b-card-footer>
      </b-form>

      <b-card-body v-if="step === 'payment'">
        <PurchaseForm
          :value="payload.resource"
          :price="formPayload.amount"
          :currency="'USD'"
          type="tip"
          :display-price="formPayload.amount | niceCurrency"
          :extra="{ notes: formPayload.notes}"
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
import { eventBus } from '@/app'
import LEDGER_CONFIG from "@/components/constants"
import PurchaseForm from '@components/payments/PurchaseForm'

// Tip timeline on another user's timeline page / feed
// Tip post on another user's timeline page / feed
// Tip post on one's own home page / feed
export default {
  name: 'SendTip',

  components: {
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
    /** 'initial' | 'payment' */
    step: 'initial',
    LEDGER_CONFIG,
    formPayload: {
      amount: LEDGER_CONFIG.MIN_TIP_IN_CENTS,
      notes: '',
    },
  }),

  created() { },

  methods: {

    async sendTip(e) {
      e.preventDefault()
      this.step = 'payment'

      // const { resource, resource_type } = this.payload 
      // let url
      // switch (resource_type) {
      //   case 'posts':
      //     url = route('posts.tip', resource.id) // tip post (resource)
      //     break
      //   case 'timelines':
      //   default:
      //     url = route('timelines.tip', resource.id) // tip timeline (resource)
      // }
      // const response = await axios.put(url, {
      //   base_unit_cost_in_cents: this.formPayload.amount,
      //   notes: this.formPayload.notes || '',
      // })
      // this.$bvModal.hide('modal-tip')
      // this.$root.$bvToast.toast(`Tip sent to ${this.tippedTimeline.slug}`, {
      //   toaster: 'b-toaster-top-center',
      //   title: 'Success!',
      // })
      // eventBus.$emit('update-originator')
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
