<template>
  <div id="preview">
    <img :src="imgPreviewUrl" class="img-fluid" />
    <see-more v-if="linkValid" :link="attrs.link"></see-more>
  </div>
</template>

<script>
import validateUrl from '@helpers/validateUrl'
import SeeMore from './SeeMore.vue'

export default {
  mounted() {
  },

  props: [
    'imgPreviewUrl',
    'attrs',
  ],

  computed: {
    linkValid() {
      return validateUrl(this.attrs.link)
    }
  },

  data: () => ({
  }),

  methods: {
  },

  components: {
    seeMore: SeeMore,
  },
}
</script>

<style scoped>
#preview {
  display: flex;
  position: relative;
  justify-content: center;
  align-items: center;
}
#preview img {
  max-width: 100%;
  max-height: 500px;
}
</style>
