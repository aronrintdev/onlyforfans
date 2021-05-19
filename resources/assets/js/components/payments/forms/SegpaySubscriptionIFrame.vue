<template>
  <div>
    <div v-if="!iframeError" class="frame-container">
      <div v-if="loading" class="loader d-flex justify-content-center align-items-center text-light">
        <fa-icon icon="spinner" spin size="2x" />
      </div>
      <div class="frame-wrapper mx-auto">
        <iframe
          ref="iframe"
          v-if="baseUrl"
          class="w-100 segpay-iframe"
          :src="baseUrl"
          @load="onLoad"
        />
      </div>
    </div>

    <div v-if="iframeError">
      <b-btn block :href="baseUrl" target="_blank" variant="primary" class="py-5 h4" style="font-size: 1.2rem;" v-text="'Click here to make payment with our trusted vendor'" />
    </div>
  </div>
</template>

<script>
/**
 * Form for adding new Segway card Payment
 */
export default {
  name: 'SegpaySubscriptionIFrame',
  props: {
    value: { type: Object, default: () =>({}) },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    /** Type of transaction: `'purchase' | 'subscription' | 'tip'` */
    type: { type: String, default: 'purchase' },
    paymentMethod: { type: Object, default: () => ({}) },
    extra: { type: Object, default: () => ({})},
  },

  data: () => ({
    baseUrl: null,
    loading: true,
    iframeError: false,
    errorTimeout: null,
    errorAfter: 10000,
    save: true,
  }),

  methods: {
    loadBaseUrl() {
      this.loading = true
      var price = this.price
      if (this.currency === 'USD') {
        price = (price / 100).toFixed(2)
      }

      this.errorTimeout = setTimeout(() => {
        this.iframeError = true
      }, this.errorAfter);

      this.axios.post(this.$apiRoute('payments.segpay.getSubscriptionUrl'), {
        price: this.price,
        item: this.value.id,
        currency: this.currency,
        method: this.paymentMethod.id,
        type: this.type,
        ...this.extra,
      })
        .then(response => {
          this.baseUrl = response.data
        })
    },

    onLoad(e) {
      this.loading = false
      clearTimeout(this.errorTimeout)
      this.iframeError = false
    },
  },

  watch: {
    save() {
      this.loadBaseUrl()
    },
  },

  mounted() {
    this.loadBaseUrl()
  }
}
</script>

<style lang="scss" scoped>
.frame-container {
  position: relative;
  min-height: 50rem;
  .frame-wrapper {
    max-width: 575px;
  }
  iframe {
    border: 0;
    min-height: 50rem;
    height: 100%;
    max-width: 575px;
  }
  .loader {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.5);
  }
}
</style>
