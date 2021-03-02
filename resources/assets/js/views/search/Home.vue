<template>
  <div class="text-center">
    Search Page<br/> Query: ( {{ query }} )
  </div>
</template>

<script>
export default {
  props: {
    query: { type: String, default: '' },
  },
}
</script>

<style lang="scss" scoped>

</style>