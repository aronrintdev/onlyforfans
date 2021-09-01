<template>
  <div class="transactions w-100">
    <Table class="w-100" />
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/Transactions/Transactions.vue
 */
import Vuex from 'vuex'
import Table from './Table'

export default {
  name: 'Transactions',

  components: {
    Table,
  },

  props: {},

  computed: {
    ...Vuex.mapState(['session_user', 'mobile', 'screenSize']),
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
