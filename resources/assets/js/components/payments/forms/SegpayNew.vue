<template>
  <div>
    <iframe
      ref="iframe"
      v-if="baseUrl"
      class="w-100 segpay-iframe"
      :src="baseUrl"
      @load="onLoad"
      @loadstart="onLoadstart"
    />
    <div v-if="iframeError">
      <b-btn block :href="buttonUrl" target="_blank" variant="primary" class="py-5 h4" style="font-size: 1.2rem;" v-text="'Click here to make payment with our trusted vendor'" />
    </div>
    
  </div>
</template>

<script>
/**
 * Form for adding new Segway card Payment
 */
export default {
  name: 'SegpayNew',
  props: {},

  data: () => ({
    baseUrl: null,
    iframeError: false,
    buttonUrl: null,
  }),

  methods: {
    loadBaseUrl() {
      var price = '20.00'
      this.axios.post(`/payment/segpay/generate-url`, { price })
        .then(response => {
          this.axios.get(response.data)
            .then(res => {
              console.log({res})
              this.baseUrl = response.data
            })
            .catch(error => {
              this.iframeError = true
              this.buttonUrl = response.data
              console.error({ error })
            })
        })
    },

    onLoad(e) {
      console.log('iframe onLoad', { e })
    },

    onLoadstart(e) {
      console.log('iframe onLoadstart', { e })
    }
  },

  mounted() {
    this.loadBaseUrl()
  }
}
</script>

<style lang="scss" scoped>
.segpay-iframe {
  min-height: 50rem;
}
</style>
