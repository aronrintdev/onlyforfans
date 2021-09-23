<template>
  <div>
    <div v-if="empty" class="d-flex align-items-center justify-content-center py-5">
      <div class="h4">{{ $t('emptyMessage') }}</div>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/Chargebacks/Chargebacks.vue
 */
import _ from 'lodash'
import Vuex from 'vuex'

export default {
  name: 'Chargebacks',

  components: {},

  props: {},

  computed: {
    empty() {
      return _.isEmpty(this.chargebacks)
    }
  },

  data: () => ({
    chargebacks: []
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "emptyMessage": "You have no chargebacks on file"
  }
}
</i18n>
