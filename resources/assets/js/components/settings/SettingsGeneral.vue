<template>
  <div v-if="!isLoading">

    <b-card title="General">
      <b-card-text>
        <b-form @submit.prevent="submitGeneral($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formGeneral">

            <b-row>
              <b-col>
                <FormTextInput ikey="firstname" v-model="formGeneral.firstname" label="First Name" :verrors="verrors" />
              </b-col>
              <b-col>
                <FormTextInput ikey="lastname" v-model="formGeneral.lastname" label="Last Name" :verrors="verrors" />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <FormTextInput ikey="username" v-model="formGeneral.username" label="Username" :verrors="verrors" :disabled="true"/>
              </b-col>
              <b-col>
                <FormTextInput ikey="email" v-model="formGeneral.email" label="E-mail" :verrors="verrors" />
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formGeneral" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formGeneral=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formGeneral=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Subscriptions" class="mt-5">
      <b-card-text>
        <b-form @submit.prevent="submitSubscriptions($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formSubscriptions">

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
              <div v-if="isEditing.formSubscriptions" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formSubscriptions=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formSubscriptions=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Localization" class="mt-5">
      <b-card-text>
        <b-form @submit.prevent="submitLocalization($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formLocalization">

            <b-row>
              <b-col>
                <FormTextInput ikey="localization.language"  v-model="formLocalization.localization.language"  label="Enter Language" :verrors="verrors" />
              </b-col>
              <b-col>
                <FormTextInput ikey="localization.timezone"  v-model="formLocalization.localization.timezone"  label="Time Zone" :verrors="verrors" />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <FormSelectInput 
                  ikey="localization.country"  
                  v-model="formLocalization.localization.country" 
                  label="Country" 
                  :verrors="verrors" 
                  :options="options.countries" 
                />
              </b-col>
              <b-col>
                <FormSelectInput 
                  ikey="localization.currency"  
                  v-model="formLocalization.localization.currency" 
                  label="Currency" 
                  :verrors="verrors" 
                  :options="options.currencies" 
                />
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formLocalization" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formLocalization=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formLocalization=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>
        </b-form>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import FormTextInput from '@components/forms/elements/FormTextInput'
import FormSelectInput from '@components/forms/elements/FormSelectInput'

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

    foo: 'foo init value',

    isEditing: {
      formGeneral: true,
      formSubscriptions: false,
      formLocalization: false,
    },

    verrors: null,

    formGeneral: {
      firstname: null,
      lastname: null,
      username: null,
      email: null,
    },
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
    formLocalization: {
      localization: { // cattrs
        language: '',
        country: '',
        timezone: '',
        currency: '',
      },
    },

    options: {
      currencies: [ 
        { value: null, text: 'Please select an option' },
        { value: 'usd', text: 'US Dollar' },
        { value: 'eur', text: 'Euro' },
        { value: 'gbp', text: 'British Pound' },
        { value: 'cad', text: 'Canadian Dollar' },
      ],
      countries: [ 
        { value: null, text: 'Please select an option' },
        { value: 'us', text: 'USA' },
        { value: 'canada', text: 'Canada' },
      ],
      timezones: [ 
        { value: null, text: 'Please select an option' },
        { value: 'America/Los_Angeles', text: '(GMT-08:00) Pacific Time (US & Canada)' },
        { value: 'US/Mountain', text: '(GMT-07:00) Mountain Time (US & Canada)' },
        { value: 'US/Central', text: '(GMT-06:00) Central Time (US & Canada)' },
        { value: 'US/Eastern', text: '(GMT-05:00) Eastern Time (US & Canada)' },
      ],
    },

  }),

  watch: {
    session_user(newVal) {
      // %FIXME: is this necessary?
      this.formGeneral.username = newVal.username
      this.formGeneral.firstname = newVal.firstname
      this.formGeneral.lastname = newVal.lastname
      this.formGeneral.email = newVal.email
    },
    user_settings(newVal) {
      if ( newVal.cattrs.subscriptions ) {
        this.formSubscriptions.subscriptions = newVal.cattrs.subscriptions
      }
      if ( newVal.hasOwnProperty('is_follow_for_free') ) {
        this.formSubscriptions.is_follow_for_free = newVal.is_follow_for_free ? 1 : 0
      }
      if ( newVal.cattrs.localization ) {
        this.formLocalization.localization = newVal.cattrs.localization
      }
    },
  },

  mounted() {
  },

  created() {
    this.formGeneral.username = this.session_user.username || ''
    this.formGeneral.firstname = this.session_user.firstname || ''
    this.formGeneral.lastname = this.session_user.lastname || ''
    this.formGeneral.email = this.session_user.email || ''

    if ( this.user_settings.cattrs.subscriptions ) {
      this.formSubscriptions.subscriptions = this.user_settings.cattrs.subscriptions
    }
    if ( this.user_settings.hasOwnProperty('is_follow_for_free') ) {
      this.formSubscriptions.is_follow_for_free = this.user_settings.is_follow_for_free ? 1 : 0
    }
    if ( this.user_settings.cattrs.localization ) {
      this.formLocalization.localization = this.user_settings.cattrs.localization
    }
  },

  methods: {

    async submitGeneral(e) {
      try {
        const response = await axios.patch(`/users/${this.session_user.id}`, this.formGeneral)
        this.isEditing.formGeneral = false
        this.verrors = null
      } catch(err) {
        this.verrors = err.response.data.errors
      }
    },

    async submitSubscriptions(e) {
      try {
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formSubscriptions)
        this.isEditing.formSubscriptions = false
        this.verrors = null
      } catch(err) {
        this.verrors = err.response.data.errors
      }
    },

    async submitLocalization(e) {
      try { 
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formLocalization)
        this.isEditing.formLocalization = false
        this.verrors = null
      } catch(err) {
        this.verrors = err.response.data.errors
      }
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  components: {
    FormTextInput,
    FormSelectInput,
  },
}
</script>

<style scoped>
</style>

