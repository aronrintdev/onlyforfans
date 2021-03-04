<template>
  <div>

    <b-card title="General">
      <b-card-text>
        <b-form @submit.prevent="submitGeneral($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formGeneral">

          <b-row>
            <b-col>
              <b-form-group id="group-username" label="Username" label-for="username">
                <b-form-input id="username" v-model="formGeneral.username" placeholder="Enter username" :disabled="true" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="group-fullname" label="Full Name" label-for="fullname">
                <b-form-input id="fullname" v-model="formGeneral.fullname" placeholder="Enter full name" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group id="group-email" label="E-mail" label-for="email">
                <b-form-input id="email" v-model="formGeneral.email" placeholder="Enter E-mail" ></b-form-input>
              </b-form-group>
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
              <b-form-group id="group-price_per_1_months" label="Subscription Price per Month" label-for="price_per_1_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                  <b-form-input id="price_per_1_months" v-model="formSubscriptions.subscriptions.price_per_1_months" placeholder="Enter price per month" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="group-price_per_3_months" label="Subscription Price per 3 Months" label-for="price_per_3_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                  <b-form-input id="price_per_3_months" v-model="formSubscriptions.subscriptions.price_per_3_months" placeholder="Enter price per 3 months" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="group-price_per_6_months" label="Subscription Price per 6 Months" label-for="price_per_6_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                  <b-form-input id="price_per_6_months" v-model="formSubscriptions.subscriptions.price_per_6_months" placeholder="Enter price per 6 months" ></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group id="group-price_per_12_months" label="Subscription Price per Year" label-for="price_per_12_months">
                <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
                  <b-form-input id="price_per_12_months" v-model="formSubscriptions.subscriptions.price_per_12_months" placeholder="Enter price per year" ></b-form-input>
                </b-input-group>
              </b-form-group>
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
              <b-form-group id="group-language" label="Language" label-for="language">
                <b-form-input id="language" v-model="formLocalization.localization.language" placeholder="Enter language" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="group-timezone" label="Time Zone" label-for="timezone">
                <b-form-select v-model="formLocalization.localization.timezone" :options="options.timezones"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group id="group-country" label="Country" label-for="country">
                <b-form-select v-model="formLocalization.localization.country" :options="options.countries"></b-form-select>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="group-currency" label="Currency" label-for="currency">
                <b-form-select v-model="formLocalization.localization.currency" :options="options.currencies"></b-form-select>
              </b-form-group>
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
//import Vuex from 'vuex';

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    //...Vuex.mapState(['vault']),
  },

  data: () => ({

    isEditing: {
      formGeneral: false,
      formSubscriptions: false,
      formLocalization: false,
    },

    formGeneral: {
      username: '',
      fullname: '',
      email: '',
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
        this.formGeneral.username = newVal.username;
        this.formGeneral.fullname = newVal.name;
        this.formGeneral.email = newVal.email;
      },
      user_settings(newVal) {
        if ( newVal.cattrs.subscriptions ) {
          this.formSubscriptions.subscriptions = newVal.cattrs.subscriptions;
        }
        if ( newVal.is_follow_for_free ) {
          this.formSubscriptions.is_follow_for_free = newVal.is_follow_for_free;
        }
        if ( newVal.cattrs.localization ) {
          this.formLocalization.localization = newVal.cattrs.localization;
        }
      },
  },

  mounted() {
  },

  created() {
  },

  methods: {

    async submitGeneral(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formGeneral);
      this.isEditing.formGeneral = false;
    },

    async submitSubscriptions(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formSubscriptions);
      this.isEditing.formSubscriptions = false;
    },

    async submitLocalization(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formLocalization);
      this.isEditing.formLocalization = false;
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

