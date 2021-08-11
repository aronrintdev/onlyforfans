<template>
  <div v-if="!isLoading">
    <b-card title="ID Verification" class="mb-3">
      <b-card-text>

        <section>
          <p v-if="verifiedStatus==='none'">Please provide your full legal name and a mobile phone number. You will receive a verification text from the "ID Merit" Service. Follow the instructions and once complete check this page again to review your status.</p>
          <p v-else-if="verifiedStatus==='pending'">Your identity verification request was successfully submitted and is pending review</p>
          <p v-else-if="verifiedStatus==='rejected'">Unfortunately we could't successfully complete the identity verification process. Please contact support.</p>
          <p v-else-if="verifiedStatus==='verified'">Congratulations! You are now verified. Please complete your sign-up by 
            <router-link :to="{ name: 'settings.banking' }">adding a bank account</router-link> 
            so you can receive payments.</p>
        </section>

        <section v-if="verifiedStatus==='none'">

          <hr />
          <b-form id="form-send-verify-request" @submit.prevent>

            <b-form-row>
              <b-form-group class="col-sm-6" label="First Name" label-for="firstname">
                <b-form-input id="firstname" v-model="form.firstname" required />
              </b-form-group>
              <b-form-group class="col-sm-6" label="Last Name" label-for="lastname">
                <b-form-input id="lastname" v-model="form.lastname" required />
              </b-form-group>
            </b-form-row>
            <b-form-row>
              <b-form-group class="col-sm-6" label="Mobile Phone" label-for="mobile" >
                <b-form-input id="mobile" v-model="form.mobile" required inputmode="numeric" pattern="[0-9]*" v-mask="'(###) ###-####'" />
              </b-form-group>
            </b-form-row>
            <!-- <b-form-row>
              <b-form-group class="col-sm-6 ">
                <b-form-checkbox
                  :disabled="!session_user.is_verified"
                  v-model="form.hasAllowedNSFW"
                >
                  Enable NSFW
                </b-form-checkbox>
              </b-form-group>
            </b-form-row> -->
            <!--
            <b-form-row>
              <b-form-group class="col-sm-4" label="Country" label-for="country">
                <b-form-select id="country" v-model="form.country" :options="options.countries" required  />
              </b-form-group>
            </b-form-row>
            <b-form-row>
              <b-form-group class="col-sm-4" label="Date of Birth" label-for="dob">
                <b-form-datepicker id="dob" v-model="form.dob" />
              </b-form-group>
            </b-form-row>
              -->

            <b-form-row class="mt-3">
              <b-col class="col-sm-12">
                <b-button @click="requestVerify" type="submit" variant="primary" class="clickme_to-submit OFF-w-100" :disabled="isProcessing">
                  <span v-if="!isProcessing">Submit</span>
                  <span v-else>
                      Sending... <fa-icon class="input-spinner" icon="spinner" spin />
                    </span>
                </b-button>
              </b-col>
            </b-form-row>

          </b-form>
        </section>

        <hr />

        <section class="d-flex align-items-center">
          <p class="mb-0">Status:</p>
          <h5 class="mb-0 ml-1"><b-badge :variant="statusBadgeVariant" class="p-2">{{ renderStatus }}</b-badge></h5> 
        </section>

        <section v-if="renderError" class="mt-3">
          <p class="mb-0 text-danger">There was a problem with your request</p>
        </section>

        <section v-if="verifiedStatus==='pending'" class="mt-3">
          <hr />
          <b-form id="form-send-check-status-request" @submit.prevent>
            <b-form-row class="mt-3">
              <b-col class="col-sm-12">
                <b-button @click="checkStatus" type="submit" variant="primary" class="clickme_to-submit OFF-w-100" :disabled="isProcessing">
                  <span v-if="!isProcessing">Check Status</span>
                  <span v-else>Sending... <fa-icon class="input-spinner" icon="spinner" spin /></span>
                </b-button>
              </b-col>
            </b-form-row>

          </b-form>
        </section>

        <!-- ++++ -->

        <section class="d-none mt-5">
          <hr />
          DEBUG INFO
          <ul>
            <li>raw status: {{ verifiedStatus }}</li>
          </ul>
        </section>

      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue' // needed to use niceCurrency filter in table formatter below
import Vuex from 'vuex'
import moment from 'moment'

export default {

  props: { },

  computed: {
    isLoading() {
      return !this.session_user && !this.user_settings
    },

    ...Vuex.mapGetters(['session_user', 'user_settings']),

    verifiedStatus() {
      if ( !this.session_user ) {
        return 'none'
      }
      return this.session_user.verified_status || 'none'
    },

    renderStatus() {
      switch (this.verifiedStatus) {
        case 'none':
          return 'Pre-Apply'
        case 'pending':
          return 'Pending'
        case 'verified':
          return 'Verified'
        case 'rejected':
          return 'Rejected'
        default:
          return 'Unknown'
      }
    },

    statusBadgeVariant() {
      switch (this.verifiedStatus) {
        case 'pending':
          return 'warning'
        case 'verified':
          return 'success'
        case 'rejected':
          return 'danger'
        case 'none':
        default:
          return 'light'
      }
    }
  },

  watch: { },

  data: () => ({

    isProcessing: false,
    renderError: false,

    form: {
      mobile: null,
      firstname: null,
      lastname: null,
      hasAllowedNSFW: false,
      //country: null,
      //dob: null,
    },
    options: {
      countries: [
        { text: 'US', value: 'US' },
      ]
    },

  }),

  methods: {
    ...Vuex.mapActions([
      'getMe',
      'getUserSettings',
    ]),

    resetForm() {
      this.form.mobile = null;
      this.form.firstname = null;
      this.form.lastname = null;
    },

    async requestVerify() {
      this.renderError = false
      //const countryCodeForUS = '1'
      this.isProcessing = true
      const payload = {
        //mobile: countryCodeForUS + this.form.mobile.replace(/\D/g,''), // only send digits, prepend US country code
        mobile: this.form.mobile.replace(/\D/g,''), // only send digits, prepend US country code
        firstname: this.form.firstname,
        lastname: this.form.lastname,
        hasAllowedNSFW: this.form.hasAllowedNSFW,
      }
      try { 
        await axios.post( this.$apiRoute('users.requestVerify'), payload )
        this.$root.$bvToast.toast('Request successfully sent!', { toaster:'b-toaster-top-center', title:'Success', variant:'success' })
        await this.getMe()
        await this.getUserSettings({ userId: this.session_user.id })
      } catch (err) {
        console.log('SettingsVerify::checkStatus()', { err })
        this.renderError = true
      }
      this.isProcessing = false
      this.resetForm()

      // %FIXME %TODO: handle failure cases?

    },

    async checkStatus() {
      this.renderError = false
      this.isProcessing = true
      const payload = { }
      try { 
        await axios.post( this.$apiRoute('users.checkVerifyStatus'), payload )
        await this.getMe()
      } catch (err) {
        console.log('SettingsVerify::checkStatus()', { err })
        this.renderError = true
      }
      this.isProcessing = false

      // %FIXME %TODO: handle failure cases?
    },
  },

  watch: {
    verifiedStatus(val) {
      if ( val==='verified' ) {
        this.$root.$bvToast.toast('Your account has been successfully verified...congratulations!', { toaster:'b-toaster-top-center', title:'Success', variant:'success' })
      }
    },
  },

  created() {
    this.form.hasAllowedNSFW = Boolean(this.user_settings.has_allowed_nsfw)
    this.getMe()
  },


}
</script>

<style scoped>
.clickme_to-submit {
  width: 9rem;
}
</style>
