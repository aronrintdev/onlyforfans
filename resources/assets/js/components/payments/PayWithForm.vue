<template>
  <div>
    <b-row>
      <b-col md="6" v-for="option in options" :key="option.system" class="mb-3">
        <b-btn block variant="light" class="d-flex py-3">
          <fa-icon :icon="option.icon" size="4x" class="mx-3" />
          <span class="description h4 align-self-center text-center flex-grow-1" v-text="$t(option.description)" />
        </b-btn>
      </b-col>
    </b-row>

  </div>
</template>

<script>
/**
 * PayWithForm for selecting other payment systems at checkout
 */
export default {
  name: 'PayWithForm',

  data: () => ({
    options: [
      {
        system: 'paypal',
        description: 'Pay with PayPal',
        icon: ['fab', 'paypal']
      },
      {
        system: 'crypto',
        description: 'Pay with Crypto',
        icon: ['fab', 'bitcoin'],
      },
    ],
  }),
}
</script>

<style lang="scss" scoped>

</style>
