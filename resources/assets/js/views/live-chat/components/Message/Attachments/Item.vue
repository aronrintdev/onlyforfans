<template>
  <component :is="component" :value="value" />
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Attachments/Item.vue
 */
import Vuex from 'vuex'
import MediaImage from './Image'
import Tip from './Tip'

export default {
  name: 'Item',

  components: {
    MediaImage,
    Tip,
  },

  props: {
    value: { type: Object, default: () => ({})},
  },

  computed: {
    component() {
      if (this.value.type === 'tip') {
        return 'Tip'
      }
      if (this.value.is_image) {
        return 'MediaImage'
      }
    },
  },

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
