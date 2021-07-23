<template>
  <div>
    <PriceSelector :label="$t('label')" :value="value" @input="v => $emit('input', v)" />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/SetPrice.vue
 */
import Vuex from 'vuex'
import PriceSelector from '@components/common/PriceSelector'

export default {
  name: 'SetPrice',

  components: {
    PriceSelector
  },

  props: {
    value: { type: Number },
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
  "en": {
    "label": "Message Price"
  }
}
</i18n>
