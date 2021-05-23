<template>
  <div class="container">
    <b-card>
      <New />
    </b-card>
  </div>
</template>

<script>
import New from '@components/statements/ach/New'

export default {
  name: 'NewAchAccount',

  components: {
    New,
  }
}
</script>
