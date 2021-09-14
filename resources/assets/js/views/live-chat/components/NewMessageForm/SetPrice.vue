<template>
  <b-alert show variant="primary" class="tag d-flex justify-content-start align-items-center">
    <fa-icon :icon="['fas', 'tag']" size="lg" class="mx-2" fixed-width />
    <PriceSelector :label="$t('label')" :value="value" autofocus @input="v => $emit('input', v)" noBottomMargin />

    <b-btn variant="link" class="p-0 ml-2" @click="$emit('clear')">
      <fa-icon :icon="['fas', 'times']" class="icon cursor-pointer fa-lg" fixed-width />
    </b-btn>
  </b-alert>
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

<style lang="scss" scoped>
.tag {
  min-height: 2.25rem;
  padding: var(--spacer-1, 0.25rem);
  margin: 0;
  padding-top: 0;
  padding-bottom: 0;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "label": "Message Price:"
  }
}
</i18n>
