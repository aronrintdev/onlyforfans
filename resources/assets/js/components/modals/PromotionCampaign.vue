<template>
  <b-form @submit="onSubmit">

    <b-card no-body>

      <b-card-header>
        <b-form-checkbox id="has-new-subscribers" v-model="hasNewSubscribers" size="lg">New subscribers</b-form-checkbox>
        <b-form-checkbox id="has-expired-subscribers" v-model="hasExpiredSubscribers" size="lg">Expired subscribers</b-form-checkbox>
      </b-card-header>

      <b-card-body>
        <b-form-group label="">
          <b-form-radio v-model="campaignType" value="discount">First month discount</b-form-radio>
          <b-form-radio v-model="campaignType" value="trial">Free trial</b-form-radio>
        </b-form-group>
        <b-row>
          <b-col>
            <label for="offer-limit">Offer limit</label>
            <!-- <b-form-select id="offer-limit" class="" v-model="offerLimit" :options="offerLimitOptions" required /> -->
            <b-form-input v-model="offerLimit" :formatter="validateData" type="text" placeholder="Leave blank for no limit"></b-form-input>
          </b-col>
          <b-col>
            <label for="offer-limit">Offer expiration</label>
            <b-form-select id="offer-days" class="" v-model="offerDays" :options="offerDaysOptions" required />
          </b-col>
        </b-row>
        <b-row class="mt-3">
          <b-col v-if="!isFreeTrial">
            <label for="offer-limit">Discount percent</label>
            <b-form-select id="discount-percent" class="" v-model="discountPercent" :options="discountPercentOptions" required />
          </b-col>
          <b-col v-if="isFreeTrial">
            <label for="offer-limit">Free trial period</label>
            <b-form-select id="trial-days" class="" v-model="trialDays" :options="trialDaysOptions" required />
          </b-col>
        </b-row>
        <b-form-group class="flex-fill mt-4">
          <b-form-input v-model="message" placeholder="Message (optional)" />
        </b-form-group>

        <hr />

        <!-- Preview Blurb -->
        <template v-if="campaignType==='discount'">
          <div>
              <h6 class="m-0 text-center">Limited offer - {{ campaignPreview.discount_percent }}% off for {{ campaignPreview.offer_days }} days!</h6>
              {{ campaignPreview | renderCampaignBlurb }}
          </div>
          <section v-if="campaignPreview" class="box-campaign-blurb mt-1">
            <article v-if="campaignPreview.message" class="tag-message d-flex align-items-center">
              <div class="user-avatar">
                <b-img rounded="circle" :src="timeline.avatar.filepath" :title="timeline.name" />
              </div>
              <div class="text-wrap py-2 w-100">
                <p class="mb-0">{{ campaignPreview.message }}</p>
              </div>
            </article>
          </section>
        </template>

        <hr />

        <!-- Details -->
        <div class="m-0">
            Original Price {{ timeline.userstats.display_prices_in_cents.subscribe_1_month | niceCurrency }}
            - Discounted to {{ applyDiscount(timeline.userstats.display_prices_in_cents.subscribe_1_month, discountPercent) | niceCurrency }}
        </div>

      </b-card-body>

      <b-card-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="hideModal">
            {{ $t('action_btns.cancel') }}
          </b-btn>
          <b-btn class="px-3" variant="primary" type="submit">
            {{ $t('action_btns.start_campaign') }}
          </b-btn>
        </div>
      </b-card-footer>

    </b-card>

  </b-form>
</template>

<script>
import { eventBus } from '@/eventBus'
import moment from 'moment'

export default {
  name: 'PromotionCampaign',

  props: {
    timeline: null,
  },

  data: () => ({
    moment: moment,
    hasNewSubscribers: true,
    hasExpiredSubscribers: false,
    campaignType: 'discount',
    offerLimit: 10,
    offerDays: 7,
    discountPercent: 5,
    trialDays: 7,
    message: '',

    //offerLimitOptions: [ { text: 'No limits', value: -1 }, ],

    offerDaysOptions: [
      { text: 'No expiration', value: 0 },
      { text: '1 day', value: 1 },
      { text: '2 days', value: 2 },
      { text: '3 days', value: 3 },
      { text: '4 days', value: 4 },
      { text: '5 days', value: 5 },
      { text: '6 days', value: 6 },
      { text: '7 days', value: 7 },
      { text: '8 days', value: 8 },
      { text: '9 days', value: 9 },
      { text: '10 days', value: 10 },
      { text: '20 days', value: 20 },
      { text: '30 days', value: 30 },
    ],
    trialDaysOptions: [
      { text: '1 day', value: 1 },
      { text: '2 days', value: 2 },
      { text: '3 days', value: 3 },
      { text: '4 days', value: 4 },
      { text: '5 days', value: 5 },
      { text: '6 days', value: 6 },
      { text: '7 days', value: 7 },
      { text: '8 days', value: 8 },
      { text: '9 days', value: 9 },
      { text: '10 days', value: 10 },
      { text: '20 days', value: 20 },
      { text: '30 days', value: 30 },
    ],
  }),

  computed: {

    isFreeTrial() {
      return this.campaignType === 'trial'
    },

    discountPercentOptions() {
      const options = []
      for ( let i = 5 ; i <= 95 ; i+= 5 ) {
        const discounted = this.applyDiscount(this.timeline.userstats.display_prices_in_cents.subscribe_1_month, i)
        if (discounted >= 300) { // $3.00
          options.push({ text: `${i}% discount`, value: i })
        }
      }
      return options
    },

    campaignPreview() {
      const tsNow = moment()
      let targetedCustomerGroup = ''
      if (this.hasNewSubscribers && this.hasExpiredSubscribers) {
        targetedCustomerGroup = 'new-and-expired'
      } else if (this.hasNewSubscribers) {
        targetedCustomerGroup = 'new'
      } else if (this.hasExpiredSubscribers) {
        targetedCustomerGroup = 'expired'
      }
      return {
        type: this.campaignType,
        discount_percent: this.discountPercent,
        created_at: tsNow,
        offer_days: this.offerDays, 
        trial_days: this.trialDays, 
        targeted_customer_group: targetedCustomerGroup,
        is_subscriber_count_unlimited: !this.offerLimit || !(this.offerLimit > 0), 
        subscriber_count: this.offerLimit,
        message: this.message,
      }
    },

  }, // computed


  methods: {
    hideModal() {
      this.$bvModal.hide('modal-promotion-campaign')
    },

    onSubmit(e) {
      e.preventDefault()

      const payload = {
        type: this.campaignType,
        has_new: this.hasNewSubscribers,
        has_expired: this.hasExpiredSubscribers,
        subscriber_count: this.offerLimit,
        offer_days: this.offerDays,
        discount_percent: this.discountPercent,
        trial_days: this.trialDays,
        message: this.message,
      }

      this.axios.post(this.$apiRoute('campaigns.store'), payload).then(response => {
        eventBus.$emit('campaign-updated', response.data.data)
        this.hideModal()
      })
    },

    validateData(value) { // leave blank for no limit
      let numV = parseInt(value, 10);
      if(numV < 0) {
        numV = 0;
      } else if (Number.isNaN(numV)) {
        numV = null;
      }
      return numV
    },

    applyDiscount(origAmountInCents, discountPercent) {
      const discountInCents = origAmountInCents * (discountPercent/100)
      return origAmountInCents - discountInCents
    }
  }, // methods

}
</script>

<style lang="scss" scoped>
.box-campaign-blurb {

  .tag-message {
    position: relative;
    margin-top: 0.3rem;
    padding: 0.2rem 0.3rem;

    .text-wrap {
      border-radius: 0.5rem;
      background: #f1f1f1;
      margin-left: 5px;
      p { 
        margin-left: 40px;
      }
    }

    .user-avatar {
      position: absolute;
      top: -5px;
      left: 0;
    }

    .user-avatar img {
      object-fit: cover;
      width: 40px;
      height: 40px;
    }
  }
}

</style>

<i18n lang="json5" scoped>
{
  "en": {
    "action_btns": {
      "start_campaign": "Start Campaign",
      "cancel": "Cancel"
    },
  }
}
</i18n>
