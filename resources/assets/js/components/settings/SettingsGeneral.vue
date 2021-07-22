<template>
  <div v-if="!isLoading">
    <b-card title="General">
      <b-card-text>
        <b-form @submit.prevent="submitGeneral($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formGeneral">
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
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting.formGeneral" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting.formGeneral" small />&nbsp;
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Localization" class="mt-5">
      <b-card-text>
        <b-form @submit.prevent="submitLocalization($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formLocalization">
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
              <div class="w-100 d-flex justify-content-end">
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
            </b-col>
          </b-row>
        </b-form>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex'
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
    isSubmitting: {
      formGeneral: false,
      formLocalization: false,
    },

    verrors: null,

    formGeneral: {
      firstname: null,
      lastname: null,
      username: null,
      email: null,
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
    user_settings(newVal) {
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

    if ( this.user_settings.cattrs.localization ) {
      this.formLocalization.localization = this.user_settings.cattrs.localization
    }
  },

  methods: {
    ...Vuex.mapActions(['getMe']),

    async submitGeneral(e) {
      this.isSubmitting.formGeneral = true

      try {
        const response = await axios.patch(`/users/${this.session_user.id}`, this.formGeneral)
        this.verrors = null

        // re-fetch Me info
        this.getMe()

        // show toaster
        this.onSuccess()
      } catch(err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formGeneral = false
    },

    async submitLocalization(e) {
      this.isSubmitting.formLocalization = true

      try {
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formLocalization)
        this.verrors = null
        this.onSuccess()
      } catch(err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formLocalization = false
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
    FormSelectInput,
  },
}
</script>

<style scoped>
</style>

