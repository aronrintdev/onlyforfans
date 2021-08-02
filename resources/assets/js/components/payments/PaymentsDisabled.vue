<template>
  <div>
    <b-alert varian="warning" show>
      <b-media>
        <template #aside>
          <fa-icon icon="exclamation-triangle" size="2x" fixed-width />
        </template>
        <p>
          {{ $t('message') }}
        </p>
      </b-media>
    </b-alert>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/payments/PaymentsDisabled.vue
 */
import Vuex from 'vuex'

export default {
  name: 'PaymentsDisabled',

  components: {},

  props: {},

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
    "message": "Payments are temporarily disable for this phase of the beta program."
  }
}
</i18n>
