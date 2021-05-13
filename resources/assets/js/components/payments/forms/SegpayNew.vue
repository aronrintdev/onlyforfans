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
            <b-form-group>
              <div class="d-flex align-items-center">
                <CardBrandIcon ref="brandIcon" :card-number="form.card.number" class="mr-3" size="2x" />
                <b-form-input
                  v-model="form.card.number"
                  v-mask="'####-####-####-####'"
                  :placeholder="$t('Card Number')"
                  pattern="\d*"
                />
              </div>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col cols="6">
            <b-form-group :label="$t('Expiration Date')" >
              <div class="d-flex align-items-center">
                <b-form-input
                  id="new-card-month"
                  v-model="form.card.expirationMonth"
                  v-mask="masks.expirationMonth"
                  placeholder="MM"
                  class=""
                  pattern="\d*"
                />
                <span class="mx-3" style="font-size:150%;" v-text="'/'" />
                <b-form-input
                  id="new-card-year"
                  v-model="form.card.expirationYear"
                  v-mask="masks.expirationYear"
                  placeholder="YY"
                  pattern="\d*"
                />
              </div>
            </b-form-group>
          </b-col>
          <b-col cols="6">
            <b-form-group :label="$t('Security Code')" >
              <b-form-input v-model="form.card.cvv" v-mask="'####'" :placeholder="$t('CVV')" pattern="\d*" />
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col lg="6">
            <b-form-group>
              <b-form-input v-model="form.customer.zip" :placeholder="$t('Postal Code')" />
            </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group>
              <b-form-input v-model="form.customer.countryCode" />
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
import { monthMask, shortYearMask } from '@helpers/masks'

export default {
  name: 'SegpayNew',

  components: {
    CardBrandIcon,
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
    },

    masks: {
      expirationMonth: monthMask,
      expirationYear: shortYearMask,
    },

    formErrors: false,

    sessionId: '',
    packageId: '',
    expirationDateTime: '',

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
            this.axios.post(this.$apiRoute('payments.segpay.fake'), {
              item: this.value.id,
              type: this.type,
              price: this.price,
              currency: this.currency,
              last_4: this.form.card.number.slice(-4),
              brand: this.$refs.brandIcon.brand,
              extra: this.extra,
            })
            this.$emit('success', 'faked')
            this.$emit('processing')
            return
          }

          console.log('onComplete')
          this.$emit('processing')
          const data = {
            sessionId: this.sessionId,
            packageId: parseInt(this.packageId),
            customer: {
              ...this.form.customer,
              email: this.session_user.email,
            },
            card: {
              ...this.form.card,
              expirationYear: parseInt(`20${this.form.card.expirationYear}`),
              expirationMonth: parseInt(this.form.card.expirationMonth),
            },
            billing: {
              pricePointId: null,
              currencyCode: this.currency,
            },
            item_type: this.type,
            item_id: this.value.id,
          }
          console.log({ data })
          window.segpay.sdk.completePayment(data, (result) => {
            console.log({ result })
            switch (result.status) {
              case 'GeneralErrors':
                console.log({ result })
              break;
              case 'ValidationErrors':
                console.log({ result })
              break;
              case 'Success':
                this.$emit('success', result.purchases)
              break;
            }
          })
        })
      })
    },

    init() {
      this.loading = false
      Promise.all([
        this.loadSegPaySdk(),
        this.getSessionId(),
      ]).then(() => { this.loading = false })
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
          this.$nextTick(() => {
            this.$bvModal.hide('modal-purchase-post')
          })
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
          this.sessionId = results.data.id
          this.packageId = results.data.packageId
          this.pageId = results.data.pageId
          this.expirationDateTime = results.data.expirationDateTime
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
    }
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
  }
}
</i18n>
