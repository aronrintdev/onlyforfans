<template>
  <div class="d-flex flex-column">
    <b-form-group
      :label="label || $t('label')"
      :state="valid"
      :invalid-feedback="validationMessage"
      :label-cols="horizontal ? 'auto' : null"
      :class="{ 'mb-0': noBottomMargin }"
    >
      <b-input-group
        :prepend="currencySymbol"
      >
        <b-form-input
          ref="price-input"
          class="position-relative"
          :class="{ 'limit-width': limitWidth, 'clearable': clearable }"
          v-model="price"
          v-mask="currencyMask"
          :state="valid"
          inputmode="decimal"
          :placeholder="showPlaceholder ? placeholder : null"
          :autofocus="autofocus"
          :disabled="disabled"
          @focus="onFocus"
          @blur="onBlur"
          @wheel.prevent="onMousewheel"
          @keypress.enter="doBlur"
        />
        <FormInputClearButton v-if="clearable && price !== ''" @clear="onClear" />
        <slot name="append"></slot>
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
 * resources/assets/js/components/common/PriceSelector.vue
 * PriceSelector
 */
import _ from 'lodash'
import createNumberMask from 'text-mask-addons/dist/createNumberMask'
import currencyCodes from 'currency-codes'
import PriceSelectorSlider from '@components/common/PriceSelectorSlider'
import FormInputClearButton from '@components/forms/elements/FormInputClearButton'

export default {
  name: 'PriceSelector',

  components: {
    PriceSelectorSlider,
    FormInputClearButton,
  },

  props: {
    /** v-model: price as integer */
    value:           { type: [Number, String] },
    min:             { type: Number, default: 300 },   // Default $3
    max:             { type: Number, default: 10000 }, // Default $100
    default:         { type: Number, default: 300 },   // Default $3
    showPlaceholder: { type: Boolean, default: true },
    showSlider:      { type: Boolean, default: false },
    interval:        { type: Number, default: 100 },
    minorMark:       { type: Number, default: 500 },
    majorMark:       { type: Number, default: 1000 },
    currency:        { type: String, default: 'USD' },
    label:           { type: String },
    autofocus:       { type: Boolean, default: false },
    limitWidth:      { type: Boolean, default: true },
    horizontal:      { type: Boolean, default: true },
    noBottomMargin:  { type: Boolean, default: false },
    disabled:        { type: Boolean, default: false },
    clearable:       { type: Boolean, default: false },
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
    placeholder() {
      return this.formatNumber(this.default)
    },
  },

  data: () => ({
    focus: false,
    valid: null,
    price: '',
    validationMessage: '',
  }),

  methods: {
    validate(soft = false,) {
      if (soft && this.valid === null) {
        return true
      }
      if ((this.price === '' || this.$parseNumber(this.price) === 0) && this.clearable) {
        this.valid = null
        return true
      }

      const value = this.price !== ''
        ? typeof this.price === 'string'
          ? Math.round(this.$parseNumber(this.price) * this.currencyModifier)
          : Math.round(this.price * this.currencyModifier)
        : this.default
      if ( value < this.min ) {
        this.valid = false
        this.validationMessage = this.$t('minError', { label: this.label || $t('label'), price: this.formatCurrency(this.min)})
        return false
      }
      if (value > this.max) {
        this.valid = false
        this.validationMessage = this.$t('maxError', { label: this.label || $t('label'), price: this.formatCurrency(this.max) })
        return false
      }
      this.valid = null
      return true
    },

    onInput(value) {
      this.validate(true)
      if (value !== '') {
        this.$emit('input', Math.round(this.$parseNumber(value) * this.currencyModifier))
      } else {
        this.$emit('input', 0)
      }
    },

    onSliderChange(value) {
      this.price = this.numberFormatter.format(value / this.currencyModifier)
      this.validate()
      this.$emit('input', Math.round(value))
    },

    onFocus(e) {
      this.focus = true
      if (this.$parseNumber(this.price) === 0) {
        this.price = ''
      }
    },

    onBlur(e) {
      if (this.price === '') {
        if (!this.clearable) {
          this.price = this.formatNumber(this.default)
        }
      }
      if (this.clearable && this.$parseNumber(this.price) === 0 || this.price === '') {
        this.price = ''
        this.validate()
        this.$emit('input', 0)
        return
      }
      this.focus = false
      this.price = this.numberFormatter.format(this.$parseNumber(this.price))
      this.validate()
      this.$emit('input', Math.round(this.$parseNumber(this.price) * this.currencyModifier))
    },

    onClear() {
      this.$emit('input', 0)
      this.$nextTick(() => {
        // Needed due to timing issue with thousands separators
        this.$emit('input', 0)
        this.price = ''
      })
    },

    onMousewheel(e) {
      var value = this.price * this.currencyModifier
      var interval = this.interval
      if (e.shiftKey) {
        interval = interval * 10
      } else if (e.altKey) {
        interval = interval / 4
      }
      if (e.wheelDelta > 0) {
        value += interval
      } else if (e.wheelDelta < 0) {
         value -= interval
      }
      value = _.clamp(value, this.min, this.max)
      this.onSliderChange(value)
    },

    doBlur() {
      this.$refs['price-input'].blur()
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
      if (this.clearable && value === '') {
        this.validate()
        this.$emit('input', 0)
        return
      }
      this.onInput(value)
    },
    value(value) {
      if (!this.focus) {
        if (this.clearable && this.$parseNumber(value) === 0) {
          this.price = ''
          return
        }
        this.price = this.formatNumber(value)
      }
    },
    valid(value) {
      this.$emit('isValid', value)
    }
  },

  mounted() {
    if (this.value < this.min ) {
      if (!this.clearable) {
        this.$emit('input', this.min)
      }
    } else {
      this.price = this.formatNumber(this.value)
    }
  }

}
</script>

<style lang="scss" scoped>
.limit-width {
  max-width: 7rem;
}
::placeholder {
  color: var(--gray-500);
  opacity: 1; /* Firefox */
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "label": "Price",
    "minError": "{label} cannot be lower than {price}",
    "maxError": "{label} cannot be higher than {price}",
    "clear": "Clear"
  }
}
</i18n>
