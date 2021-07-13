<template>
  <div>
    <component :is="component" :value="value" />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Attachment/index.vue
 */
import Vuex from 'vuex'
import Tip from './Tip'

export default {
  name: 'Attachment',

  components: {
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
