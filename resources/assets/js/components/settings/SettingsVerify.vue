<template>
  <div v-if="!isLoading">
    <b-card title="Verification" class="mb-3">
      <b-card-text>

        <section>
          <b-form @submit.prevent>

            <b-form-row>
              <b-form-group class="col-sm-6" label="First Name" label-for="firstname">
                <b-form-input id="firstname" v-model="form.firstname" required ></b-form-input>
              </b-form-group>
              <b-form-group class="col-sm-6" label="Last Name" label-for="lastname">
                <b-form-input id="lastname" v-model="form.lastname" required ></b-form-input>
              </b-form-group>
            </b-form-row>
            <b-form-row>
              <b-form-group class="col-sm-6" label="Mobile Phone" label-for="mobile">
                <b-form-input id="mobile" v-model="form.mobile" required ></b-form-input>
              </b-form-group>
            </b-form-row>
            <!--
            <b-form-row>
              <b-form-group class="col-sm-4" label="Country" label-for="country">
                <b-form-select id="country" v-model="form.country" :options="options.countries" required ></b-form-select>
              </b-form-group>
            </b-form-row>
            <b-form-row>
              <b-form-group class="col-sm-4" label="Date of Birth" label-for="dob">
                <b-form-datepicker id="dob" v-model="form.dob"></b-form-datepicker>
              </b-form-group>
              -->
            </b-form-row>

            <b-form-row class="mt-3">
              <b-col class="col-sm-1">
                <b-button @click="requestVerify" type="submit" variant="primary" class="w-100">Submit</b-button>
              </b-col>
            </b-form-row>

          </b-form>
        </section>

        <hr />

        <section>
          Status: {{ verifiedStatus }}
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
      return !this.session_user
    },

    ...Vuex.mapGetters(['session_user', 'user_settings']),

    verifiedStatus() {
      if ( !this.session_user ) {
        return '';
      }
      switch (this.session_user.verified_status) {
        case 'none':
          return 'Pre-Apply';
        case 'pending':
          return 'Pending';
        case 'verified':
          return 'Verified';
        case 'rejected':
          return 'Rejected';
        default:
          return 'Unknown';
      }
    },
  },

  watch: { },

  data: () => ({

    form: {
      mobile: null,
      firstname: null,
      lastname: null,
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
    ]),

    resetForm() {
      this.form.mobile = null;
      this.form.firstname = null;
      this.form.lastname = null;
    },

    async requestVerify() {
      //const countryCodeForUS = '1'
      const payload = {
        //mobile: countryCodeForUS + this.form.mobile.replace(/\D/g,''), // only send digits, prepend US country code
        mobile: this.form.mobile.replace(/\D/g,''), // only send digits, prepend US country code
        firstname: this.form.firstname,
        lastname: this.form.lastname,
      }
      const response1 = await axios.post( this.$apiRoute('users.requestVerify'), payload )
      const response2 = await this.getMe()
      this.resetForm()

      // %TODO: handle failure cases?

    },
  },

  created() {
    this.getMe()
  },


}
</script>

<style scoped>
</style>
