<template>
  <div>

    <b-card title="General">
      <b-card-text>
        <b-form @submit.prevent="submitGeneral($event)" @reset="onReset">

          <b-row>
            <b-col>
              <b-form-group id="input-group-username" label="Username" label-for="username">
                <b-form-input id="username" v-model="formGeneral.username" placeholder="Enter username" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="input-group-fullname" label="Full Name" label-for="fullname">
                <b-form-input id="fullname" v-model="formGeneral.fullname" placeholder="Enter full name" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group id="input-group-email" label="E-mail" label-for="email">
                <b-form-input id="email" v-model="formGeneral.email" placeholder="Enter E-mail" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row align-h="end" class="mt-3">
            <b-col sm="2">
              <b-button type="submit" class="w-100" variant="primary">Save</b-button>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Subscriptions" class="mt-5">
      <b-card-text>
        <b-form @submit.prevent="submitSubscriptions($event)" @reset="onReset">

          <b-row>
            <b-col>
              <b-form-group id="input-group-price_per_1_months" label="Subscription Price per Month" label-for="price_per_1_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                <b-form-input id="price_per_1_months" v-model="formSubscriptions.price_per_1_months" placeholder="Enter price per month" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="input-group-price_per_3_months" label="Subscription Price per 3 Months" label-for="price_per_3_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                <b-form-input id="price_per_3_months" v-model="formSubscriptions.price_per_3_months" placeholder="Enter price per 3 months" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="input-group-price_per_6_months" label="Subscription Price per 6 Months" label-for="price_per_6_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                <b-form-input id="price_per_6_months" v-model="formSubscriptions.price_per_6_months" placeholder="Enter price per 6 months" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="input-group-price_per_12_months" label="Subscription Price per Year" label-for="price_per_12_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                <b-form-input id="price_per_12_months" v-model="formSubscriptions.price_per_12_months" placeholder="Enter price per year" ></b-form-input>
                </b-input-group>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
            </b-col>
          </b-row>

          <b-row align-h="end" class="mt-3">
            <b-col sm="2">
              <b-button type="submit" class="w-100" variant="primary">Save</b-button>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Localization" class="mt-5">
      <b-card-text>
        <b-form @submit.prevent="submitLocalization($event)" @reset="onReset">

          <b-row>
            <b-col>
              <b-form-group id="input-group-language" label="Language" label-for="language">
                <b-form-input id="language" v-model="formLocalization.language" placeholder="Enter language" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="input-group-timezone" label="Time Zone" label-for="timezone">
                 <b-form-select v-model="formLocalization.timezone" :options="options.timezones"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group id="input-group-country" label="Country" label-for="country">
                 <b-form-select v-model="formLocalization.country" :options="options.countries"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row align-h="end" class="mt-3">
            <b-col sm="2">
              <b-button type="submit" class="w-100" variant="primary">Save</b-button>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
//import Vuex from 'vuex';

export default {

  props: {
  },

  computed: {
    //...Vuex.mapState(['vault']),
  },

  data: () => ({
    formGeneral: {
      username: '',
      fullname: '',
      email: '',
    },
    formSubscriptions: {
      price_per_1_months: null,
      price_per_3_months: null,
      price_per_6_months: null,
      price_per_12_months: null,
      is_follow_for_free: null,
      referral_rewards: '',
    },
    formLocalization: {
      language: '',
      country: '',
      timezone: '',
      currency: '',
    },

    options: {
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

  mounted() {
  },

  created() {
  },

  methods: {

    async submitGeneral(e) {
      const response = await axios.post(`/settings`, this.formGeneral);
    },

    async submitSubscriptions(e) {
      const response = await axios.post(`/settings`, this.formSubscriptions);
    },

    async submitLocalization(e) {
      const response = await axios.post(`/settings`, this.formLocalization);
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  components: {
  },
}
</script>

<style scoped>
</style>

