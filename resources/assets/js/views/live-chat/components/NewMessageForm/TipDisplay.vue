<template>
  <b-alert show variant="success" class="tag d-flex flex-nowrap justify-content-start align-items-center">
    <fa-icon icon="envelope-open-dollar" fixed-width size="lg" class="mx-2" />
    <span class="mr-1">
      {{ $t('prepend') }}
    </span>
    <strong @click="$emit('open')" class="cursor-pointer">
      {{ value | niceCurrency }}
    </strong>
    <b-btn variant="link" class="p-0 ml-2" @click="$emit('clear')">
      <fa-icon icon="times" size="lg" fixed-width />
    </b-btn>
  </b-alert>
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

<style lang="scss" scoped>
.tag {
  min-height: 2.25rem;
  padding: var(--spacer-1, 0.25rem);
  margin: 0;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "prepend": "Tip:"
  }
}
</i18n>
