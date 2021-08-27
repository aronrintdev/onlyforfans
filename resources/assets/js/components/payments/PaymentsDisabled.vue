<template>
  <div>
    <b-alert varian="warning" show>
      <b-media vertical-align="center">
        <template #aside>
          <fa-layers class="fa-3x">
            <fa-icon icon="ban" fixed-width  />
            <fa-icon icon="dollar-sign" transform="shrink-6" fixed-width />
          </fa-layers>
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
    "message": "Payments are temporarily disabled at the moment. Please check back later."
  }
}
</i18n>
