<template>
  <div v-if="!isLoading">
    <b-card title="Subscription">
      <b-card-text>
        <b-form @submit.prevent="submitSubscriptions($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formSubscriptions">
            <b-row>
              <b-col sm="12" md="6">
                <FormTextInput itype="currency" ikey="subscriptions.price_per_1_months"  v-model="subscriptions.price_per_1_months"  label="Price per Month" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_3_months"  v-model="subscriptions.price_per_3_months"  label="Price per 3 Months" :verrors="verrors" disabled />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_6_months"  v-model="subscriptions.price_per_6_months"  label="Price per 6 Months" :verrors="verrors" disabled />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_12_months" v-model="subscriptions.price_per_12_months" label="Price per Year" :verrors="verrors" disabled />
              </b-col>
              <b-col sm="12" md="6">
                <b-form-group id="group-is_follow_for_free" label="Allow Follow for Free" label-for="is_follow_for_free">
                  <b-form-checkbox
                    id="is_follow_for_free"
                    v-model="formSubscriptions.is_follow_for_free"
                    name="is_follow_for_free"
                    value=1
                    unchecked-value=0
                    switch size="lg"></b-form-checkbox>
                </b-form-group>
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner class="mr-1" v-if="isSubmitting.formSubscriptions" small />
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Profile Promotion Campaign" class="mt-5">
      <b-row class="mt-3" v-if="!activeCampaign">
        <b-col>
          <p><small class="text-muted">Offer a free trial or a discounted subscription on your profile for a limited number of new or already expired subscribers.</small></p>
          <div class="w-100 d-flex justify-content-end">
            <b-button @click="startCampaign" class="px-4" variant="primary">Start Promotion Campaign</b-button>
          </div>
        </b-col>
      </b-row>

      <b-row v-if="activeCampaign">
        <b-col class="mt-3">
          <h6 v-if="activeCampaign.type === 'trial'">Limited offer - Free trial for {{ activeCampaign.trial_days }} days!</h6>
          <h6 v-if="activeCampaign.type === 'discount'">Limited offer - {{ activeCampaign.discount_percent }}% off for 31 days!</h6>
          <p><small class="text-muted">{{ campaignBlurb }}</small></p>
          <div class="w-100 d-flex justify-content-end">
            <b-button @click="showStopModal" class="px-4" variant="primary">Stop Promotion Campaign</b-button>
          </div>
        </b-col>
      </b-row>
    </b-card>

    <b-modal id="modal-stop-campaign" v-model="isStopModalVisible" size="md" title="Stop Promotion Campaign" >
      <p>Are you sure you want to stop your profile promotion campaign?</p>
      <template #modal-footer>
        <b-button @click="hideStopModal" type="cancel" variant="secondary">Cancel</b-button>
        <b-button @click="stopCampaign" variant="danger">Stop Campaign</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import moment from 'moment'
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import FormTextInput from '@components/forms/elements/FormTextInput'

export default {
  props: {
    session_user: null,
    user_settings: null,
    timeline: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },

    campaignBlurb() {
      if ( !this.activeCampaign ) {
        return null
      }
      const { created_at, offer_days, targeted_customer_group } = this.activeCampaign
      let str = 'For '
      switch ( targeted_customer_group ) {
        case 'new':
          str += 'new'
          break
        case 'expired':
          str += 'expired'
          break
        case 'new-and-expired':
          str += 'new & expired'
          break
      }
      str += ' subscribers'
      str += ` \u2022  ends ${moment(created_at).add(offer_days, 'days').format('MMM D')}`
      str += ` \u2022  ${this.activeCampaign.subscriber_count} left`
      return str
    },

  }, // computed

  data: () => ({
    isSubmitting: {
      formSubscriptions: false,
    },

    verrors: null,
    subscriptions: { // cattrs
      price_per_1_months: null,
      price_per_3_months: null,
      price_per_6_months: null,
      price_per_12_months: null,
      referral_rewards: '',
    },
    formSubscriptions: {
      is_follow_for_free: null,
    },

    activeCampaign: null,
    isStopModalVisible: false,
  }),

  watch: {
    user_settings(newVal) {
      if ( newVal.cattrs.subscriptions ) {
        this.subscriptions = newVal.cattrs.subscriptions
      }
      if ( newVal.hasOwnProperty('is_follow_for_free') ) {
        this.formSubscriptions.is_follow_for_free = newVal.is_follow_for_free ? 1 : 0
      }
    },
  },

  mounted() {
  },

  created() {
    if ( this.user_settings.cattrs.subscriptions) {
      this.subscriptions = {
        ...this.user_settings.cattrs.subscriptions
      }
    }
    if ( this.user_settings.hasOwnProperty('is_follow_for_free') ) {
      this.formSubscriptions.is_follow_for_free = this.user_settings.is_follow_for_free ? 1 : 0
    }

    eventBus.$on('campaign-updated', campaign => {
      this.activeCampaign = campaign
    })

    this.getActiveCampaign()
  },

  methods: {
    ...Vuex.mapActions(['getUserSettings', 'getMe']),

    async submitSubscriptions(e) {
      this.isSubmitting.formSubscriptions = true
      try {
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, {
          ...this.formSubscriptions,
          name: this.timeline.name,
          subscriptions: this.subscriptions,
        })

        this.getUserSettings({ userId: this.session_user.id })
        this.verrors = null
        this.onSuccess()
      } catch(err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formSubscriptions = false
    },

    async getActiveCampaign() {
      const response = await axios.get(this.$apiRoute('campaigns.active'))
      this.activeCampaign = response.data.data
    },

    startCampaign() {
      eventBus.$emit('open-modal', {
        key: 'modal-promotion-campaign',
      })
    },

    showStopModal() {
      this.isStopModalVisible = true
    },

    hideStopModal() {
      this.isStopModalVisible = false
    },

    async stopCampaign() {
      await axios.post(this.$apiRoute('campaigns.stop'))
      this.activeCampaign = null
      this.hideStopModal()
    },

    onReset(e) {
      e.preventDefault()
    },

    onSuccess() {
      this.$root.$bvToast.toast('Settings have been updated successfully!', {
        toaster: 'b-toaster-top-center',
        title: 'Success',
        variant: 'success',
      })
    }
  },

  components: {
    FormTextInput,
  },
}
</script>

<style scoped>
</style>

