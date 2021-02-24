<template>
  <div>
      <h1>Messages</h1>
  </div>
</template>

<script>
/**
 * Messages Dashboard View
 */
export default {
  //
  mounted() {
      console.log('------ rendered');
      this.axios.get('/chat-messages').then((response) => {
        console.log('-- response data', response);
      })
  }
}
</script>

<style lang="scss" scoped>

</style>
