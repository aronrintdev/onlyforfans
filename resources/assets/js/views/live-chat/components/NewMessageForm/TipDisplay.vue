<template>
  <div class="h2">
    <b-badge variant="success" class="d-flex">
      Tip: {{ value | niceCurrency }}
      <div class="cursor-pointer ml-3">
        <fa-icon icon="times" fixed-width @click="$emit('clear')" />
      </div>
    </b-badge>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/TipDisplay.vue
 */
import Vuex from 'vuex'

export default {
  name: 'TipDisplay',

  components: {},

  props: {
    value: { type: Object, default: () => ({ amount: 0, currency: 'USD'}) },
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
