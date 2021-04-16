<template>
  <div class="d-flex flex-column">
    <b-form-group
      :label="$t('label')"
      :state="valid"
      :invalid-feedback="validationMessage"
    >
      <b-input-group :prepend="currencySymbol">
        <b-form-input
          class="input"
          v-model="price"
          v-mask="currencyMask"
          :state="valid"
          @focus="onFocus"
          @blur="onBlur"
        />
      </b-input-group>
    </b-form-group>
    <div v-if="showSlider" class="d-flex mb-3">
      <PriceSelectorSlider
        :value="value"
        :min="min"
        :max="max"
        :interval="interval"
        :format-currency="formatCurrency"
        @input="onSliderChange"
      />
    </div>

  </div>
</template>

<script>
/**
 * PriceSelector
 */
import _ from 'lodash'
import createNumberMask from 'text-mask-addons/dist/createNumberMask'
import currencyCodes from 'currency-codes'
import PriceSelectorSlider from '@components/common/PriceSelectorSlider'

export default {
  name: 'PriceSelector',

  components: {
    PriceSelectorSlider,
  },

  props: {
    /** v-model: price as integer */
    value:      { type: Number },
    min:        { type: Number, default: 300 },
    max:        { type: Number, default: 5000 },
    showSlider: { type: Boolean, default: false },
    interval:   { type: Number, default: 100 },
    minorMark:  { type: Number, default: 500 },
    majorMark:  { type: Number, default: 1000 },
    currency:   { type: String, default: 'USD' },
  },

  computed: {
    sliderOptions() {
      //
    },
    currencyDigits() {
      return currencyCodes.code(this.currency).digits
    },
    currencyModifier() {
      return Math.pow(10, this.currencyDigits)
    },
    currencySymbol() {
      return _.find(this.currencyFormatter.formatToParts(), [ 'type', 'currency' ]).value
    },
    currencyMask() {
      return createNumberMask({
        prefix: '',
        allowDecimal: true,
        includeThousandsSeparator: false,
        thousandsSeparatorSymbol: ',',
        decimalSymbol: '.',
        decimalLimit: this.currencyDigits,
        allowNegative: false,
        allowLeadingZeroes: true,
      })
    },
    currencyFormatter() {
      return new Intl.NumberFormat(navigator.languages, { style: 'currency', currency: this.currency })
    },
    numberFormatter() {
      return new Intl.NumberFormat(navigator.languages, { style: 'decimal', minimumFractionDigits: this.currencyDigits })
    },
  },

  data: () => ({
    valid: null,
    price: '',
    validationMessage: '',
  }),

  methods: {
    validate(soft = false,) {
      if (soft && this.valid === null) {
        return
      }
      const value = this.price !== ''
        ? typeof this.price === 'string'
          ? this.$parseNumber(this.price) * this.currencyModifier
          : this.price * this.currencyModifier
        : 0
      if ( value < this.min ) {
        this.valid = false
        this.validationMessage = this.$t('minError', { price: this.formatCurrency(this.min)})
        return
      }
      if (value > this.max) {
        this.valid = false
        this.validationMessage = this.$t('maxError', { price: this.formatCurrency(this.max) })
        return
      }
      this.valid = null
      return
    },
    onInput(value) {
      this.validate(true)
      if (value !== '') {
        this.$emit('input', this.$parseNumber(value) * this.currencyModifier)
      } else {
        this.$emit('input', 0)
      }
    },
    onSliderChange(value) {
      this.price = this.numberFormatter.format(value / this.currencyModifier)
      this.$emit('input', value)
    },
    onFocus(e) {
      if (this.price === '0') {
        this.price = ''
      }
    },
    onBlur(e) {
      this.price = this.numberFormatter.format(this.price)
      this.validate()
      this.$emit('input', this.$parseNumber(this.price) * this.currencyModifier)
    },

    parse(value) {
      return this.$parseNumber(value) * this.currencyModifier
    },

    formatCurrency(value) {
      return this.currencyFormatter.format(value / this.currencyModifier)
    },
    formatNumber(value) {
      return this.numberFormatter.format(value / this.currencyModifier)
    },
  },

  watch: {
    price(value) {
      this.onInput(value)
    },
    value(val) {
      this.price = val / this.currencyModifier
    }
  },

  // mounted() {
  //   if (this.value < this.min ) {
  //     this.price = this.numberFormatter.format(this.min / this.currencyModifier)
  //   }
  // }

}
</script>

<style lang="scss" scoped>
.input {
  max-width: 7rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "label": "Price",
    "minError": "Price cannot be lower than {price}",
    "maxError": "Price cannot be higher than {price}"
  }
}
</i18n>
