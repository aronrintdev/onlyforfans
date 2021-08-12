<template>
  <div style="">
    <b-img-lazy :src="value.filepath" thumbnail height="10rem" />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Attachment/Image.vue
 */
import Vuex from 'vuex'

export default {
  name: 'Image',

  components: {},

  props: {
    value: { type: Object, default: () => ({}) },
  },

  computed: {},

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
