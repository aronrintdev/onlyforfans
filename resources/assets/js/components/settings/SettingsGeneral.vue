<template>
  <div v-if="!isLoading">
    <b-card :title="mobile ? null : $t('title')">
      <b-card-text>
        <b-form @submit.prevent="submitGeneral($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formGeneral">
            <b-row>
              <b-col lg="6">
                <FormTextInput ikey="username" v-model="formGeneral.username" label="Username" :verrors="verrors" :disabled="true"/>
              </b-col>
              <b-col lg="6">
                <FormTextInput ikey="email" v-model="formGeneral.email" label="E-mail" :verrors="verrors" :disabled="true" />
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <small class="text-secondary">* Changing the username or email is disabled during the beta testing phase.</small>
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3 mb-3 mb-md-0">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting.formGeneral" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting.formGeneral" class="mr-1" small />
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
              <b-col sm="12" md="6">
                <FormSelectInput
                  ikey="localization.language"
                  v-model="formData.language"
                  label="Language"
                  :verrors="verrors"
                  :options="options.languages"
                />
              </b-col>
              <b-col sm="12" md="6">
                <FormSelectInput
                  ikey="localization.timezone"
                  v-model="formData.timezone"
                  label="Time Zone"
                  :verrors="verrors"
                  :options="options.timezones"
                />
              </b-col>
            </b-row>

            <b-row>
              <b-col sm="12" md="6">
                <FormSelectInput 
                  ikey="localization.country"  
                  v-model="formData.country" 
                  label="Country" 
                  :verrors="verrors" 
                  :options="options.countries" 
                />
              </b-col>
              <b-col sm="12" md="6">
                <FormSelectInput 
                  ikey="localization.currency"  
                  v-model="formData.currency" 
                  label="Currency" 
                  :verrors="verrors" 
                  :options="options.currencies" 
                />
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3 mb-3 mb-md-0">
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
import Timezones from '@helpers/timezones.json'
import AllLanguages from '@helpers/languages.json'
import AllCountries from '@helpers/countries.json'
import AllCurrencies from '@helpers/currencies.json'

export default {
  props: {
    session_user: null,
    user_settings: null,
    timeline: null,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    isLoading() {
      return !this.session_user || !this.user_settings || !this.timeline
    },
  },

  data: () => ({
    isSubmitting: {
      formGeneral: false,
      formLocalization: false,
    },

    verrors: null,

    formGeneral: {
      username: null,
      email: null,
    },
    formData: { // cattrs
      language: null,
      country: null,
      timezone: null,
      currency: null,
    },

    options: {
      currencies: [ 
        { value: null, text: 'Please select an option' },
        ...AllCurrencies,
      ],
      languages: [
        { value: null, text: 'Please select an option' },
        ...AllLanguages,
      ],
      countries: [ 
        { value: null, text: 'Please select an option' },
        ...AllCountries
      ],
      timezones: [ 
        { value: null, text: 'Please select an option' },
        { value: 'us' , text: 'US time'},
        ...Timezones,
      ],
    },

  }),

  watch: {
    user_settings(newVal) {
      if ( newVal.cattrs.localization ) {
        this.formData = newVal.cattrs.localization
      }
    },
  },

  mounted() {
  },

  created() {
    this.formGeneral.username = this.session_user.username || ''
    this.formGeneral.email = this.session_user.email || ''

    if ( this.user_settings.cattrs.localization ) {
      this.formData = this.user_settings.cattrs.localization
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
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, {
          localization: { // cattrs
            ...this.formData,
          },
        })
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

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Account",
  }
}
</i18n>
