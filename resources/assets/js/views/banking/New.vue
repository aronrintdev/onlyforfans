<template>
  <div class="container-xl">
    <b-card>
      <NewForm />
    </b-card>
  </div>
</template>

<script>
import NewForm from '@components/banking/accounts/New'
export default {
  name: 'NewBankingAccount',
  components: {
    NewForm,
  },
}
</script>
