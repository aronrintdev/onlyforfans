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
        <div v-if="processing" class="overlay d-flex justify-content-center align-items-center">
          <fa-icon icon="spinner" spin size="2x" />
        </div>
        <b-row>
          <b-col lg="6">
            <b-form-group label="First Name" >
              <b-form-input v-model="form.customer.firstName" trim :placeholder="$t('First Name')" />
            </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group label="Last Name" >
              <b-form-input v-model="form.customer.lastName" trim :placeholder="$t('Last Name')" />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col>
            <b-form-group :label="$t('Card Number')" :state="formErrors.cardNumber ? true : null">
              <div class="d-flex align-items-center">
                <CardBrandIcon
                  v-if="mode === 'manual'"
                  ref="brandIcon"
                  :card-number="form.card.number"
                  class="mr-3"
                  size="2x"
                />
                <b-form-input
                  v-if="mode === 'manual'"
                  v-model="form.card.number"
                  v-mask="'####-####-####-####'"
                  :placeholder="$t('Card Number')"
                  pattern="\d*"
                />
                <div v-else ref="cardNumber" class="w-100" />
              </div>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col cols="6">
            <b-form-group :label="$t('Expiration Date')" :state="formErrors.expiration ? true : null">
              <div class="d-flex align-items-center">
                <b-form-input
                  v-if="mode === 'manual'"
                  id="new-card-month"
                  v-model="form.card.expirationMonth"
                  v-mask="masks.expirationMonth"
                  placeholder="MM"
                  class=""
                  pattern="\d*"
                  :state="formErrors.month ? true : null"
                />
                <div v-else ref="cardMonth" />
                <span class="mx-3" style="font-size:150%;" v-text="'/'" />
                <b-form-input
                  v-if="mode === 'manual'"
                  id="new-card-year"
                  v-model="form.card.expirationYear"
                  v-mask="masks.expirationYear"
                  placeholder="YY"
                  pattern="\d*"
                  :state="formErrors.year ? true : null"
                />
                <div v-else ref="cardYear" class="w-100" />
              </div>
            </b-form-group>
          </b-col>
          <b-col cols="6">
            <b-form-group :label="$t('Security Code')" :state="formErrors.cvv ? true : null">
              <b-form-input
                v-if="mode === 'manual'"
                v-model="form.card.cvv"
                v-mask="'####'"
                :placeholder="$t('CVV')"
                pattern="\d*"
              />
              <div v-else ref="cardCvv" class="w-100" />
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col lg="6">
            <b-form-group>
              <b-form-input v-model="form.customer.zip" :placeholder="$t('Postal Code')" :state="formErrors.zip ? true : null" />
            </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group :state="formErrors.country ? true : null">
              <CountrySelectInput v-model="form.customer.countryCode" :label="null" />
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col cols="6">
            <b-form-group :label="$t('nickname')">
              <b-form-input v-model="form.nickname" />
            </b-form-group>
          </b-col>
          <b-col cols="6" class="d-flex align-items-center">
            <b-form-checkbox v-model="form.card_is_default">
              <div class="font-size-small">{{ $t('isDefault') }}</div>
            </b-form-checkbox>
          </b-col>
        </b-row>

        <b-row class=" m-3">
          <b-col>
            <div ref="segpayTerms" class="w-100" />
          </b-col>
        </b-row>

        <b-row class=" m-3">
          <b-col>
            <div ref="segpayDescriptor" class="w-100" />
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
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import CountrySelectInput from '@components/forms/elements/CountrySelectInput'
import CardBrandIcon from './CardBrandIcon'
import Skeleton from './SegpayNewSkeleton'
import { monthMask, shortYearMask } from '@helpers/masks'

export default {
  name: 'SegpayNew',

  components: {
    CardBrandIcon,
    CountrySelectInput,
    Skeleton,
  },

  props: {
    /** Item being purchased, subscripted to, or tipped */
    value: { type: Object, default: () =>({}) },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    /** Type of transaction: `'purchase' | 'subscription' | 'tip'` */
    type: { type: String, default: 'purchase' },
    extra: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'timeline' ]),

    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    form: {
      customer: {
        firstName: '',
        lastName: '',
        zip: '',
        countryCode: 'US',
      },
      card: {
        number: '',
        expirationYear: '',
        expirationMonth: '',
        cvv: '',
      },
      nickname: '',
      card_is_default: true,
    },

    masks: {
      expirationMonth: monthMask,
      expirationYear: shortYearMask,
    },

    formErrors: {},

    sessionId: '',
    packageId: '',
    expirationDateTime: '',
    whitesite: '',

    mode: 'manual', // manual | segments

    loading: true,
    error: false,
    processing: false,
  }),

  methods: {
    guessDefaultValues() {
      var name = this.timeline.name.split(' ')
      if ( name.length === 2 ) {
        this.form.customer.firstName = name[0]
        this.form.customer.lastName  = name[1]
      }
    },

    formValidate() {
      return new Promise((resolve, reject) => {
        // TODO: Add validation
        resolve()
      })
    },

    onComplete() {
      this.formValidate().then(() => {
        this.loadSegPaySdk().then(() => {
          if (this.sessionId === 'faked') {
            eventBus.$emit('error', { message: "Invalid System Mode", })
            return
          }

          this.$emit('processing')

          if (this.type === 'tip') {
            // Create tip model first to send to Segpay
            this.axios.post(this.$apiRoute('tips.store'), {
              tippable_id: this.value.id,
              amount: this.price,
              currency: this.currency,
              message: this.extra ? this.extra.message : null,
            }).then(response => {
              this.completePayment('tip', response.data.id)
            }).catch(error => {
              this.$emit('error', error)
              eventBus.$emit('error', { message: this.$t('error'), })
            })
          } else {
            this.completePayment()
          }
        })
      })
    },

    completePayment(type, itemId) {
      var data = {
        sessionId: this.sessionId,
        packageId: parseInt(this.packageId),
        customer: {
          ...this.form.customer,
          email: this.session_user.email,
        },
        billing: {
          // pricePointId: null,
          currencyCode: this.currency,
        },
        userData: {
          item_type: type || this.type,
          item_id: itemId || this.value.id,
          user_id: this.session_user.id,
          nickname: this.form.nickname,
          card_is_default: this.form.card_is_default ? '1' : '0',
        },
      }
      if (this.whitesite) {
        data.userData.whitesite = this.whitesite
      }

      if (this.mode === 'manual') {
        data = { ...data,
          card: {
            ...this.form.card,
            expirationYear: parseInt(`20${this.form.card.expirationYear}`),
            expirationMonth: parseInt(this.form.card.expirationMonth),
          },
        }
      }
      window.segpay.sdk.completePayment(data, result => {
        this.$log.debug('completePayment', { result })
        // Temp
        console.log('completePayment', {result})
        switch (result.status) {
          case 'GeneralErrors':
            this.$log.warn('GeneralErrors', { result })
            this.$emit('stopProcessing')
            this.$emit('errors')
            this.handleValidationErrors(result.validationErrors)
          break;
          case 'ValidationErrors':
            this.$log.warn('ValidationErrors', { result })
            this.$emit('stopProcessing')
            this.$emit('errors')
            this.handleValidationErrors(result.validationErrors)
          break;
          case 'Success':
            this.$emit('success', result.purchases)
          break;
        }
      })
    },

    initSegpaySegments() {
      if (!window.segpay) {
        return
      }

      var data = {
        sessionId: this.sessionId,
        packageId: parseInt(this.packageId),
        segments: {
          shared: {
            remoteStylesheetUrls: [
              '/css/app.css'
            ]
          },
          information: {
            terms: {
              htmlElement: this.$refs.segpayTerms,
              styleClasses: ['text-center'],
            },
            descriptor: {
              htmlElement: this.$refs.segpayDescriptor,
              styleClasses: ['text-center'],
            },
          },
        },
      }

      if ( this.mode === 'segments' ) {
        data.segments.card = {
          number: {
            htmlElement: this.$refs.cardNumber,
            styleClasses: ['form-control', 'w-100'],
          },
          expirationMonth: {
            htmlElement: this.$refs.cardMonth,
            styleClasses: ['form-control', 'w-100'],
          },
          expirationYear: {
            htmlElement: this.$refs.cardYear,
            styleClasses: ['form-control', 'w-100'],
          },
          cvv: {
            htmlElement: this.$refs.cardCvv,
            styleClasses: ['form-control', 'w-100'],
          },
        }
      }

      window.segpay.sdk.initializePayment(
        data,
        value => { this.$log.debug('segpay.sdk.initializePayment callback', { value }) }
      )

      this.getSessionId()
    },

    clearFormErrors() {
      this.formErrors = {}
    },

    init() {
      this.loading = false
      Promise.all([
        this.loadSegPaySdk(),
        this.getSessionId(),
      ]).then(() => {
        this.loading = false
        this.$nextTick(this.initSegpaySegments)
      })
      .catch(error => {
        eventBus.$emit('error', this, error)
        this.error = true
        this.loading = false
      })
      this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
        if (modalId === 'modal-purchase-post' && this.processing) {
          bvEvent.preventDefault()
        }
      })
      this.$echo.private(this.purchasesChannel).listen('ItemPurchased', e => {
        if (e.item_id === this.value.id) {
          this.processing = false
          // this.$nextTick(() => {
          //   this.$bvModal.hide('modal-purchase-post')
          // })
        }
      })
    },

    getSessionId() {
      return new Promise((resolve, reject) => {
        this.axios.post(route('payments.segpay.getPaymentSession'), {
          item: this.value.id,
          type: this.type,
          price: this.price,
          currency: this.currency,
        }).then(results => {
          if (results.data.id === null) {
            reject('Session Id is null')
          }
          this.sessionId = results.data.id
          this.packageId = results.data.packageId
          this.pageId = results.data.pageId
          this.expirationDateTime = results.data.expirationDateTime
          this.whitesite = results.data.whitesite
          resolve()
        }).catch(error => reject(error))
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
    },

    handleValidationErrors(errors) {
      for(var error of errors) {
        console.log('error Code: ', error.code)
        console.log('error Message: ', error.message)
        // This information is from the documentation Segpay provided
        switch (error.code) {
          case 210000: // Customer Address Missing
          case 210001: // Customer Address CardNumberDetected
            break
          case 210100: // Customer Age Younger Than 18
            break
          case 210200: // Customer City Missing
          case 210201: // Customer City Card Number Detected
          case 210300: // Customer Country Missing
           break
          case 210400: // Customer Email Invalid
          case 210401: // Customer Email InvalidDomain
          case 210402: // Customer Email Missing
            break
          case 210500: // Customer First Name Missing
            this.formErrors.firstName = 'invalid'
            break
          case 210501: // Customer First Name Spaces
            this.formErrors.firstName = 'invalid'
            break
          case 210502: // Customer First Name Card Number Detected
            this.formErrors.firstName = 'invalid'
            break
          case 210600: // Customer Last Name Missing
            this.formErrors.lastName = 'invalid'
            break
          case 210601: // Customer Last Name Spaces
            this.formErrors.lastName = 'invalid'
            break
          case 210602: // Customer Last Name Card Number Detected
            this.fromErrors.lastName = 'invalid'
            break;
          case 210700: // Customer Phone Invalid
          case 210701: // Customer_Phone_Missing
          case 210702: // Customer_Phone_CardNumberDetected
          case 210800: // Customer_State_Missing
            break
          case 210900: // Customer_Zip_Invalid
            this.formErrors.zip = 'invalid'
            break
          case 210901: // Customer_Zip_Missing
            this.formErrors.zip = 'invalid'
            break
          case 210902: // Customer_Zip_CardNumberDetected
            this.formErrors.zip = 'invalid'
            break
          case 211000: // Customer_Language_Missing
          case 211001: // Customer_Language_Unsupported
          case 220000: // Customer_BirthDate_Invalid
          case 220100: // User_Name_InvalidCharacters
          case 220200: // User_Name_Missing
          case 220201: // User_Name_Spaces
          case 220202: // User_Name_TooShort
          case 220203: // User_Name_CardNumberDetected
          case 220300: // User_Password_InvalidCharacters
          case 220301: // User_Password_Missing
          case 220302: // User_Password_Spaces
          case 220303: // User_Password_TooShort
          case 220304: // User_Password_CardNumberDetected
            break
          case 230000: // Card_Cvv_InvalidCvv3
            this.formErrors.cvv = 'invalid'
            break
          case 230001: // Card_Cvv_InvalidCvv4
            this.formErrors.cvv = 'invalid'
            break
          case 230002: // Card_Cvv_Missing
            this.formErrors.cvv = 'invalid'
            break
          case 230003: // Card_Cvv_NonNumeric
            this.formErrors.cvv = 'invalid'
            break
          case 230100: // Card_ExpirationDate_Expired
            break
          case 230101: // Card_ExpirationDate_Missing
            break
          case 230102: // Card_ExpirationMonth_Missing
            this.formErrors.month = 'invalid'
            break
          case 230103: // Card_ExpirationYear_Missing
            this.formErrors.year = 'invalid'
            break
          case 230104: // Card_ExpirationMonth_Invalid
            this.formErrors.month = 'invalid'
            break
          case 230105: // Card_ExpirationYear_Invalid Invalid card expiration year
            this.formErrors.year = 'invalid'
            break
          case 230200: // Card_Number_Invalid The credit card number is invalid.
            this.formErrors.cardNumber = 'invalid'
            break
          case 230201: // Card_Number_Missing Card number is missing
            this.formErrors.cardNumber = 'invalid'
            break
          case 230202: // Card_Number_UndefinedNetwork Undefined card number network
          case 230203: // Card_Number_UnsupportedNetwork Not supported card number network
            break
          case 240000: // Bank_AccountName_Missing You must enter the bank account holder name.
          case 240100: // Bank_AccountNumber_Invalid The bank account number is invalid.
          case 240200: // Bank_Name_Missing You must select a bank.
          case 250000: // Billing_PricePoint_Missing Price point not found
          case 250001: // Billing_PricePoint_Invalid Invalid price point
          case 250002: // Billing_PricePoint_DynamicConfigurationMissing Dynamic pricing configuration not found
          case 250003: // Billing_PricePoint_DynamicConfigurationOutOfRange Either transaction amount or period outside of dynamic configuration limits
          case 250004: // Billing_PricePoint_DynamicConfigurationMismatch Dynamic pricing configuration type mismatch
          case 250100: // Billing_Currency_Missing Currency is missing
          case 250101: // Billing_Currency_Unsupported Unsupported currency
          case 250200: // Billing_Package_Missing Package not found
          case 250201: // Billing_Package_Invalid Invalid package
          case 250300: // Billing_Merchant_Missing Merchant not found
          case 250301: // Billing_Merchant_Invalid Invalid merchant
          case 250400: // Billing_MerchantUrl_Missing Merchant URL not found
          case 250401: // Billing_MerchantUrl_Invalid Invalid merchant URL
          case 250500: // Billing_ProcessorPool_Missing
          case 250501: // Billing_ProcessorPool_Invalid Invalid processor pool
          case 250600: // Billing_PaymentType_Unsupported Not supported payment type
          case 250700: // Billing_PackagePricePointBinding_Missing E-ticket not found
          case 250701: // Billing_PackagePricePointBinding_Invalid Invalid E-ticket
          case 260000: // Processing_InvalidConfiguration Invalid payment processing configuration
          case 260001: // Processing_PaymentGatewayError Payment gateway error
          default:
            break
        }
      }
    },

  },

  watch: {
    mode(val) {
      this.init()
    },
  },

  mounted() {
    this.guessDefaultValues()
    this.init()
  }
}
</script>

<style lang="scss" scoped>
.overlay {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.35);
  color: var(--white, #ffffff);
  z-index: 100;
}
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
    "nickname": "Save card as",
    "isDefault": "Save as default payment method",
    "error": "There was an error processing your payment."
  }
}
</i18n>
