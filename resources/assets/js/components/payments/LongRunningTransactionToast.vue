<template>
  <b-toast :title="$t('title')" variant="warning">
    <b-media>
      <template #aside>
        <fa-icon icon="exclamation-triangle" size="2x" />
      </template>
      {{ $t('message') }}
    </b-media>
  </b-toast>
</template>

<script>
/**
 * resources/assets/js/components/payments/LongRunningTransactionToast.vue
 */
import Vuex from 'vuex'

export default {
  name: 'LongRunningTransactionToast',

  components: {},

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

  watchers: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "message": "This transaction has been processing for a long time, we will update you when it is complete.",
    "title": "Long Running Transaction"
  }
}
</i18n>
