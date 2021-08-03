<template>
  <div class="card card-default">
    <div class="card-header">Preview</div>
    <div v-bind:style="{ backgroundColor: attrs.color }" class="card-body">
      <VueMarkdown :html="false" :source="attrs.contents || ''" />
      <see-more v-if="linkValid" :link="attrs.link"></see-more>
    </div>
  </div>
</template>

<script>
import validateUrl from '@helpers/validateUrl'
import SeeMore from './SeeMore.vue'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  mounted() {

  },

  props: [
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
    VueMarkdown,
  },
}
</script>

<style scoped>
body main .card {
  height: 70vh;
  max-height: 350px;
}
</style>
