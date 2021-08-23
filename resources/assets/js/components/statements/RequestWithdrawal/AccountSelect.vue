<template>
  <div>
    <div class="text-muted">
      {{ $t('select') }}
    </div>
    <b-list-group>
      <b-list-group-item
        v-for="item in accounts"
        :key="item.id"
        @click="$emit('selected', item)"
        class="cursor-pointer"
      >
        <Display :value="item" :selected="item.id === selected" />
      </b-list-group-item>
    </b-list-group>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/RequestWithdrawal/AccountSelect.vue
 */
import Vuex from 'vuex'
import Display from '@components/banking/accounts/Display'

export default {
  name: 'AccountSelect',

  components: {
    Display,
  },

  props: {
    accounts: { type: Array, default: () => ([]) },
    selected: { type: String, default: '' },
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
    "select": "Select an account:"
  }
}
</i18n>
