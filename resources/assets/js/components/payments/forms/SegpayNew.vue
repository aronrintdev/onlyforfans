<template>
  <div class="new-card-form">
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <Skeleton />
      </template>
      <div v-if="error">
        <b-alert show variant="danger" class="text-center" v-text="$t('An Error Has Occurred')" />
      </div>
      <div v-else>
        <b-row>
          <b-col lg="6">
            <b-form-group label="First Name" >
              <b-form-input v-model="firstName" trim :placeholder="$t('First Name')" />
            </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group label="Last Name" >
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
            <b-form-group :label="$t('Expiration Date')" >
              <div class="d-flex align-items-center">
                <b-form-input id="new-card-month" v-model="expiration.month" v-mask="'##'" placeholder="MM" class="" pattern="\d*" />
                <span class="mx-3" style="font-size:150%;" v-text="'/'" />
                <b-form-input id="new-card-year" v-model="expiration.year" v-mask="'##'" placeholder="YY" pattern="\d*" />
              </div>
            </b-form-group>
          </b-col>
          <b-col cols="6">
            <b-form-group :label="$t('Security Code')" >
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
            <b-btn block variant="success" @click="onComplete">
              {{ $t('Finish') }}
            </b-btn>
          </b-col>
        </b-row>
      </div>
    </b-skeleton-wrapper>
  </div>
</template>

<script>
/**
 * New Card Form
 */
import { eventBus } from '@/app'
import Vuex from 'vuex'
import CardBrandIcon from './CardBrandIcon'
import Skeleton from './SegpayNewSkeleton'
export default {
  name: 'SegpayNew',

  components: {
    CardBrandIcon,
    Skeleton,
  },

  props: {
    value: { type: Object, default: () =>({}) },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
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

    sessionId: '',
    packageId: '',
    loading: true,
    error: false,
  }),

  methods: {
    loadDefaultValues() {
      var name = this.timeline.name.split(' ')
      this.firstName = name[0] || ''
      this.lastName = name[1]
    },

    onComplete() {
      this.loadSegPaySdk()
      .then(() => {
        window.segpay.sdk.completePayment({
          sessionId: this.sessionId,
          packageId: this.packageId,
          customer: {
            firstName: this.firstName,
            lastName: this.lastName,
            zip: this.zip,
            countryCode: this.country,
            email: this.session_user.email,
          },
          card: {
            number: this.cardNumber,
            expirationYear: this.expiration.year,
            expirationMonth: this.expiration.month,
            cvv: this.securityCode,
          },
        }, (result) => {
          switch (result.status) {
            case 'GeneralErrors':
              //
            break;
            case 'ValidationErrors':
              //
            break;
            case 'Success':
              this.$emit('success', result.purchases)
            break;
          }
        })
      })

    },

    init() {
      Promise.all([
        this.loadSegPaySdk(),
        this.getSessionId(),
      ])
        .then(() => { this.loading = false })
        .catch(error => {
          eventBus.$emit('error', this, error)
          this.error = true
          this.loading = false
        })
    },

    getSessionId() {
      return new Promise((resolve, reject) => {
        this.axios.post(route('payments.segpay.getPaymentSession'), { item: this.value.id, price: this.price })
          .then(results => {
            this.sessionId = results.data.sessionId
            this.pageId = results.data.pageId
            this.expirationDateTime = results.data.expirationDateTime
            resolve()
          })
          .catch(error => reject(error))
      })
    },

    loadSegPaySdk() {
      return new Promise((resolve, reject) => {
        if (window.segpay) {
          resolve()
          return
        }
        let script = document.createElement('script')
        script.onload = () => {
          resolve()
        }
        script.async = true
        this.axios.get('https://embedding.segpay.com/client/v1/sdk.js/metadata')
          .then(response => {
            script.src = `https://embedding.segpay.com/client/v1/sdk.js?v=${response.data.hash}`
            script.integrity = response.data.integrity
            script.crossOrigin = 'anonymous'
            document.head.appendChild(script)
          })
          .catch(error => reject(error))
      })
    }
  },

  mounted() {
    this.loadDefaultValues()
    this.init()
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
    "Finish": "Complete Transaction",
    "An Error Has Occurred": "An Error Has Occurred",
  }
}
</i18n>
