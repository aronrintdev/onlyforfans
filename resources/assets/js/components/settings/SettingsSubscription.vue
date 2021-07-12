<template>
  <div v-if="!isLoading">
    <b-card title="Subscription">
      <b-card-text>
        <b-form @submit.prevent="submitSubscriptions($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formSubscriptions">
            <b-row>
              <b-col>
                <FormTextInput itype="currency" ikey="subscriptions.price_per_1_months"  v-model="formSubscriptions.subscriptions.price_per_1_months"  label="Price per Month" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_3_months"  v-model="formSubscriptions.subscriptions.price_per_3_months"  label="Price per 3 Months" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_6_months"  v-model="formSubscriptions.subscriptions.price_per_6_months"  label="Price per 6 Months" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_12_months" v-model="formSubscriptions.subscriptions.price_per_12_months" label="Price per Year" :verrors="verrors" />
              </b-col>
              <b-col>
                <b-form-group id="group-is_follow_for_free" label="Follow for Free?" label-for="is_follow_for_free">
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
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Profile promotion campaign" class="mt-5">
      <b-row class="mt-3">
        <b-col>
          <p><small class="text-muted">Offer a free trial or a discounted subscription on your profile for a limited number of new or already expired subscribers.</small></p>
          <div class="w-100 d-flex justify-content-center">
            <b-button @click="startPromotionCampaign" class="w-25 ml-3" variant="primary">Start Promotion Campaign</b-button>
          </div>
        </b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import FormTextInput from '@components/forms/elements/FormTextInput'

export default {
  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  data: () => ({
    isSubmitting: {
      formSubscriptions: false,
    },

    verrors: null,

    formSubscriptions: {
      is_follow_for_free: null,
      subscriptions: { // cattrs
        price_per_1_months: null,
        price_per_3_months: null,
        price_per_6_months: null,
        price_per_12_months: null,
        referral_rewards: '',
      },
    },

  }),

  watch: {
    user_settings(newVal) {
      if ( newVal.cattrs.subscriptions ) {
        this.formSubscriptions.subscriptions = newVal.cattrs.subscriptions
      }
      if ( newVal.hasOwnProperty('is_follow_for_free') ) {
        this.formSubscriptions.is_follow_for_free = newVal.is_follow_for_free ? 1 : 0
      }
    },
  },

  mounted() {
  },

  created() {
    if ( this.user_settings.cattrs.subscriptions ) {
      this.formSubscriptions.subscriptions = this.user_settings.cattrs.subscriptions
    }
    if ( this.user_settings.hasOwnProperty('is_follow_for_free') ) {
      this.formSubscriptions.is_follow_for_free = this.user_settings.is_follow_for_free ? 1 : 0
    }
  },

  methods: {
    ...Vuex.mapActions(['getMe']),

    async submitSubscriptions(e) {
      this.isSubmitting.formSubscriptions = true

      try {
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formSubscriptions)
        this.verrors = null
        this.onSuccess()
      } catch(err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formSubscriptions = false
    },

    startPromotionCampaign() {
      eventBus.$emit('open-modal', {
        key: 'modal-promotion-campaign',
      })
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

