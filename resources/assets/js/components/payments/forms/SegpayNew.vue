<template>
  <div>
    <iframe ref="iframe" v-if="baseUrl" class="w-100 segpay-iframe" :src="baseUrl" @load="onLoad" @loadstart="onLoadstart"></iframe>
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
  }),

  methods: {
    loadBaseUrl() {
      this.baseUrl = 'https://secure2.segpay.com/billing/poset.cgi?x-eticketid=199133:26943'
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
