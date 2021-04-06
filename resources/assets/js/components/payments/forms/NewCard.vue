<template>
  <div class="new-card-form">
    <b-row>
      <b-col lg="6">
        <b-form-group
          label="First Name"
        >
          <b-form-input v-model="firstName" trim :placeholder="$t('First Name')" />
        </b-form-group>
      </b-col>
      <b-col lg="6">
        <b-form-group
          label="Last Name"
        >
          <b-form-input v-model="lastName" trim :placeholder="$t('Last Name')" />
        </b-form-group>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <b-form-group>
          <div class="d-flex align-items-center">
            <CardBrandIcon :card-number="cardNumber" class="mr-3" size="2x" />
            <b-form-input v-model="cardNumber" v-mask="'####-####-####-####'" :placeholder="$t('Card Number')" pattern="\d*" />
          </div>
        </b-form-group>
      </b-col>
    </b-row>
    <b-row>
      <b-col cols="6">
        <b-form-group
          :label="$t('Expiration Date')"
        >
          <div class="d-flex align-items-center">
            <b-form-input id="new-card-month" v-model="expiration.month" v-mask="'##'" placeholder="MM" class="" pattern="\d*" />
            <span class="mx-3" style="font-size:150%;" v-text="'/'" />
            <b-form-input id="new-card-year" v-model="expiration.year" v-mask="'##'" placeholder="YY" pattern="\d*" />
          </div>
        </b-form-group>
      </b-col>
      <b-col cols="6">
        <b-form-group
          :label="$t('Security Code')"
        >
          <b-form-input v-model="securityCode" v-mask="'####'" :placeholder="$t('CVV')" pattern="\d*" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col lg="6">
        <b-form-group>
          <b-form-input v-model="postalCode" :placeholder="$t('Postal Code')" />
        </b-form-group>
      </b-col>
      <b-col lg="6">
        <b-form-group>
          <b-form-input v-model="country" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col cols="12">
        <b-btn block variant="success">
          {{ $t('Finish') }}
        </b-btn>
      </b-col>
    </b-row>

  </div>
</template>

<script>
/**
 * New Card Form
 */
import Vuex from 'vuex'
import CardBrandIcon from './CardBrandIcon'
export default {
  name: 'NewCard',

  components: {
    CardBrandIcon,
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'timeline' ]),
  },

  data: () => ({
    firstName: '',
    lastName: '',
    cardNumber: '',
    expiration: {
      month: '',
      year: '',
    },
    securityCode: '',
    postalCode: '',
    country: 'US',
  }),

  methods: {
    loadDefaultValues() {
      var name = this.timeline.name.split(' ')
      this.firstName = name[0] || ''
      this.lastName = name[1]

    },
    submit() {
      this.$emit('submit', this.$data)
    },
  },

  mounted() {
    this.loadDefaultValues()
  }
}
</script>

<style lang="scss" scoped>

</style>

<i18n lang="json5">
{
  "en": {
    "First Name": "First Name",
    "Last Name": "Last Name",
    "Card Number": "Card Number",
    "Expiration Date": "Expiration Date",
    "Security Code": "Security Code",
    "CVV": "CVV",
    "Postal Code": "Zip Code",
    "Finish": "Complete Transaction"
  }
}
</i18n>
