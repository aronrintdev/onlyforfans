<template>
  <div>
    <form v-on:submit.prevent novalidate>
      <div class="card card-default mt-3">
        <div class="card-header">Swipe Up Link</div>
        <b-form-input class="swipe-up-link" type="url" v-model="attrs.link" :state="urlState"></b-form-input>
      </div>
      <div class="superbox-ctrl d-sm-flex justify-content-around mt-3 mb-3 mb-sm-0">
        <button @click="shareStory()" type="submit" class="btn btn-primary mb-1 mb-sm-0">Share to Story</button>
        <button @click="doCancel()" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script>
import { eventBus } from '@/eventBus'
import validateUrl from '@helpers/validateUrl';

export default {
  mounted() {
  },

  props: [
    'attrs',
  ],

  computed: {
    urlState() {
      if (!this.attrs.link) return null
      return validateUrl(this.attrs.link)
    }
  },

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
.swipe-up-link {
  border-width: 0px;
}
</style>
