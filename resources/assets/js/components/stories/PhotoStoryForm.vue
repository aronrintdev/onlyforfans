<template>
  <div>
    <form v-on:submit.prevent>
      <div class="superbox-ctrl d-sm-flex justify-content-around mt-3 mb-3 mb-sm-0">
        <button @click="shareStory()" type="submit" class="btn btn-primary mb-1 mb-sm-0">Share to Story</button>
        <button @click="doCancel()" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script>
import { eventBus } from '../../app';

export default {
  mounted() {
  },

  props: [
    'attrs',
  ],

  data: () => ({
  }),

  methods: {
    doCancel() {
      this.$emit('do-cancel')
    },
    shareStory() {
      eventBus.$emit('share-story')
    },
  },

  components: {
  },
}
</script>

<style scoped>
form .superbox-ctrl .btn {
  width: 100%;
}
@media (min-width: 576px) { 
  form .superbox-ctrl .btn {
    width: 45%;
  }
}
</style>
