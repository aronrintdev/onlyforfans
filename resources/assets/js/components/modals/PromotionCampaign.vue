
<template>
  <b-form @submit="onSubmit">
    <b-card no-body>
      <b-card-header>
        <b-form-checkbox id="has-new-subscribers" v-model="hasNewSubscribers" size="lg">New subscribers</b-form-checkbox>
        <b-form-checkbox id="has-expired-subscribers" v-model="hasExpiredSubscribers" size="lg">Expired subscribers</b-form-checkbox>
      </b-card-header>
      <b-card-body>
        <b-form-group label="">
          <b-form-radio v-model="campaignType" value="first_month">First month discount</b-form-radio>
          <b-form-radio v-model="campaignType" value="free_trial">Free trial</b-form-radio>
        </b-form-group>
        <b-row>
          <b-col>
            <label for="offer-limit">Offer limit</label>
            <b-form-select id="offer-limit" class="" v-model="offerLimit" :options="offerLimitOptions" required />
          </b-col>
          <b-col>
            <label for="offer-limit">Offer expiration</label>
            <b-form-select id="offer-expiration" class="" v-model="offerExpiration" :options="offerExpOptions" required />
          </b-col>
        </b-row>
        <b-row class="mt-3">
          <b-col v-if="!isFreeTrial">
            <label for="offer-limit">Discount percent</label>
            <b-form-select id="discount-percent" class="" v-model="discountPercent" :options="discountPercentOptions" required />
          </b-col>
          <b-col v-if="isFreeTrial">
            <label for="offer-limit">Free trial period</label>
            <b-form-select id="trial-period" class="" v-model="trialPeriod" :options="trialPeriodOptions" required />
          </b-col>
        </b-row>
        <b-form-group class="flex-fill mt-4">
          <b-form-input v-model="message" placeholder="Message (optional)" />
        </b-form-group>
      </b-card-body>
      <b-card-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="onCancel">
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
import { eventBus } from '@/app';

export default {
  name: 'PromotionCampaign',

  data: () => ({
    hasNewSubscribers: false,
    hasExpiredSubscribers: false,
    campaignType: 'first_month',
    offerLimit: 10,
    offerExpiration: 7,
    discountPercent: 5,
    trialPeriod: 7,
    message: '',

    offerLimitOptions: [
      { text: 'No limits', value: 0 },
      { text: '1 subscriber', value: 1 },
      { text: '2 subscribers', value: 2 },
      { text: '3 subscribers', value: 3 },
      { text: '4 subscribers', value: 4 },
      { text: '5 subscribers', value: 5 },
      { text: '6 subscribers', value: 6 },
      { text: '7 subscribers', value: 7 },
      { text: '8 subscribers', value: 8 },
      { text: '9 subscribers', value: 9 },
      { text: '10 subscribers', value: 10 },
      { text: '20 subscribers', value: 20 },
      { text: '30 subscribers', value: 30 },
      { text: '40 subscribers', value: 40 },
      { text: '50 subscribers', value: 50 },
      { text: '60 subscribers', value: 60 },
      { text: '70 subscribers', value: 70 },
      { text: '80 subscribers', value: 80 },
      { text: '90 subscribers', value: 90 },
      { text: '100 subscribers', value: 100 },
    ],
    offerExpOptions: [
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
    trialPeriodOptions: [
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
    discountPercentOptions: [
      { text: '5% discount', value: 5 },
      { text: '10% discount', value: 10 },
      { text: '15% discount', value: 15 },
      { text: '20% discount', value: 20 },
      { text: '25% discount', value: 25 },
      { text: '30% discount', value: 30 },
    ],
  }),

  computed: {
    isFreeTrial() {
      return this.campaignType === 'free_trial'
    }
  },

  methods: {
    onCancel() {
      this.$bvModal.hide('modal-promotion-campaign')
    },

    onSubmit(e) {
      e.preventDefault()

      this.axios.post(this.$apiRoute('users.startCampaign')).then(response => {
        // TODO: add campaign calling
      })
    },
  },
}
</script>

<style lang="scss" scoped>

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