<template>
  <b-card class="form mx-auto">
    <PurchaseForm />
  </b-card>
</template>

<script>
/**
 * Make Payment view
 */
import PurchaseForm from '@components/payments/PurchaseForm'

export default {
  name: 'MakePayment',
  components: {
    PurchaseForm,
  },
}
</script>

<style lang="scss" scoped>
.form {
  max-width: 50rem;
}
</style>
