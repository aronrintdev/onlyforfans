<template>
  <div>
    <uploader></uploader>
  </div>
</template>

<script>
import Uploader from '../media/Uploader.vue';

export default {
  mounted() {
  },
  //props: ['username'],

  data: () => ({
  }),

  methods: {
  },

  components: {
    uploader: Uploader,
  },
}
</script>

<style scoped>
</style>
