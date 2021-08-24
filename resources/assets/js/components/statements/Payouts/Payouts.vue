<template>
  <b-card>
    <Table v-if="!mobile" :items="transactions"></Table>
  </b-card>
</template>

<script>
/**
 * resources/assets/js/components/statements/Payouts/Payouts.vue
 */
import Vuex from 'vuex'
import Table from './Table'

export default {
  name: 'Payouts',

  components: {
    Table,
  },

  props: {},

  computed: {
    ...Vuex.mapState(['mobile']),
  },

  data: () => ({
    loading: false,
    transactions: [],
  }),

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
