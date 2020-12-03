<template>
  <div>
    <uploader></uploader>
  </div>
</template>

<script>
import { eventBus } from '../../app';
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
