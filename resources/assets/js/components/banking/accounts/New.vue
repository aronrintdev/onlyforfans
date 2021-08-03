<template>
  <div>
    <div class="h2 text-center text-md-left" v-text="$t('title')" />
    <b-row>
      <b-col md="6">
        <div class="d-lg-flex align-items-center mb-5">
          <!-- Name -->
          <b-form-group
            class="flex-fill"
            :label="$t('form.name.label')"
            :disabled="form.company_name !== ''"
          >
            <b-form-input
              v-model="form.name"
              :disabled="form.company_name !== ''"
              :placeholder="$t('form.name.placeholder')"
            />
          </b-form-group>

          <div class="mx-3 text-nowrap flex-shrink-1 text-center">
            -- or --
          </div>

          <!-- Company Name -->
          <b-form-group
            class="flex-fill"
            :label="$t('form.company_name.label')"
          >
            <b-form-input
              v-model="form.company_name"
              :disabled="form.name !== ''"
              :placeholder="$t('form.company_name.placeholder')"
            />
          </b-form-group>
        </div>

        <b-form-group
          :label="$t('form.beneficiary_name.label')"
        >
          <b-form-input v-model="form.beneficiary_name" :placeholder="$t('form.beneficiary_name.placeholder')" />
        </b-form-group>

        <CountrySelectInput v-model="form.residence_country" :label="$t('form.residence_country.label')" />
        <hr class="d-block d-md-none" />
      </b-col>

      <b-col md="6">
        <b-form-group
          :label="$t('form.routing_number.label')"
        >
          <div class="position-relative">
            <b-form-input
              v-model="form.routing_number"
              :placeholder="$t('form.routing_number.placeholder')"
              v-mask="'#########'"
              inputmode="numeric"
              pattern="[0-9]*"
            />
            <div
              v-if="routingNumberWarning"
              class="bank-warning text-warning"
            >
              <fa-icon icon="exclamation-triangle" />
            </div>
          </div>
          <b-collapse v-model="routingNumberWarning">
            <b-alert show variant="warning">
              <b-media>
                <template #aside>
                  <fa-icon icon="exclamation-triangle" size="3x" />
                </template>
                {{ $t('form.routing_number.warningTooltip') }}
              </b-media>
            </b-alert>
          </b-collapse>
        </b-form-group>

        <b-form-group
          :label="$t('form.account_number.label')"
        >
          <b-form-input
            v-model="form.account_number"
            v-mask="numericMask"
            :placeholder="$t('form.account_number.placeholder')"
            inputmode="numeric"
            pattern="[0-9]*"
          />
        </b-form-group>

        <b-form-group
          :label="$t('form.bank_name.label')"
        >
          <div class="position-relative">
            <b-form-input
              v-model="form.bank_name"
              :placeholder="$t('form.bank_name.placeholder')"
              :disabled="form.routing_number.length < 9"
            />
            <fa-icon v-if="bankGuessProcessing" class="input-spinner" icon="spinner" spin />
          </div>

        </b-form-group>

        <CountrySelectInput v-model="form.bank_country" :label="$t('form.bank_country.label')" />
      </b-col>
    </b-row>

    <b-row class="mt-5">
      <b-col>
        <b-btn variant="success" block :disabled="processing">
          <span v-if="processing">
            <fa-icon icon="spinner" spin />
          </span>
          <span v-else>
            <fa-icon icon="plus" fixed-width />
            {{ $t('form.submit') }}
          </span>
        </b-btn>
      </b-col>
    </b-row>

  </div>
</template>

<script>
/**
 * New ACH Account Form
 */
import { eventBus } from '@/eventBus'
import _ from 'lodash'
import Vuex from 'vuex'
import CountrySelectInput from '@components/forms/elements/CountrySelectInput'
import { numericMask } from '@helpers/masks'

export default {
  name: "NewAch",

  components: {
    CountrySelectInput,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    numericMask() {
      return numericMask
    }
  },

  data: () => ({
    form: {
      name: '',
      company_name: '',
      type: '', // Individual | Business
      residence_country: 'US',
      beneficiary_name: '',
      bank_name: '',
      routing_number: '',
      account_number: '',
      bank_country: 'US',
      currency: 'USD',
    },
    routingNumberWarning: false,

    processing: false,
    bankGuessProcessing: false,
  }),

  methods: {
    ...Vuex.mapActions('banking', [ 'bankFromRoutingNumber' ]),

    submit() {
      this.processing = true
      const data = {
        ...this.form,
        name: this.form.name || this.form.company_name,
        type: this.form.name ? 'individual' : 'business',
      }
      this.$axios.put(this.$apiRoute('ach_accounts.store'), { data })
      .then(response => {
        this.processing = false
      })
      .catch(error => {
        eventBus.$emit('error', { error, message: this.$t('formError') })
        this.processing = false
      })
    },

    guessBank() {
      this.bankGuessProcessing = true
      this.bankFromRoutingNumber(this.form.routing_number)
      .then(data => {
        console.log({data})
        if (data.code === 200 && data.customer_name) {
          this.form.bank_name = data.customer_name
        } else {
          this.routingNumberWarning = true
        }
        this.bankGuessProcessing = false
      })
      .catch(error => {
        this.bankGuessProcessing = false
      })
    },
  },


  watch: {
    'form.routing_number': function(value, prev) {
      if (value.length === 9 && prev.length < 9) {
        this.guessBank()
      }
      if (value.length < 9) {
        this.routingNumberWarning = false
      }
    },
  },

}
</script>

<style lang="scss" scoped>
.input-spinner {
  position: absolute;
  top: 0.75rem;
  right: 0.5rem;
}
.bank-warning {
  position: absolute;
  top: 0.5rem;
  right: 0.75rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Add Bank Account",
    "formError": "There was an issue submitting your account.",
    "form": {
      "name": {
        "label": "Name",
        "placeholder": "Name",
      },
      "company_name": {
        "label": "Company Name",
        "placeholder": "Company Name",
      },
      "residence_country": {
        "label": "Country of Residence",
      },
      "beneficiary_name": {
        "label": "Name of Beneficiary",
        "placeholder": " "
      },
      "bank_name": {
        "label": "Bank Name",
        "placeholder": " ",
      },
      "routing_number": {
        "label": "Routing Number",
        "placeholder": " ",
        "warningTooltip": "We were unable to look up this routing number. Please double check that it is correct before adding this account."
      },
      "account_number": {
        "label": "Account Number",
        "placeholder": " ",
      },
      "bank_country": {
        "label": "Bank Country of Origin"
      },
      "submit": "Add Account"
    }
  }
}
</i18n>
