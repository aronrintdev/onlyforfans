<template>
  <b-media vertical-align="center">
    <template #aside>
      <slot name="prepend">
        <div class="position-relative" v-b-tooltip.hover="value.default ? ($t('primary')) : false">
          <span v-if="value.default" class="text-primary primary-dot">
            <fa-icon icon="circle" />
          </span>
          <transition-group :name="selected ? 'icon-flip-right' : 'icon-flip-left'" mode="out-in">
            <fa-icon v-if="selected" key="selected" icon="check" fixed-width size="3x" class="text-success" />
            <fa-icon v-else key="not-selected" icon="university" fixed-width size="3x" class="text-muted" />
          </transition-group>
        </div>
      </slot>
    </template>
    <div class="d-flex">
      <slot>
        <div>
          <div class="h4" v-text="value.bank_name" />
          <div v-text="$t(`account_type.${value.account_type}`, { last_4: value.account_number_last_4 })" />
        </div>
      </slot>

      <div class="ml-auto align-self-center">
        <slot name="append"></slot>
      </div>
    </div>
  </b-media>
</template>

<script>
/**
 * resources/assets/js/components/banking/accounts/Display.vue
 *
 * Display for payment account information
 */
import Vuex from 'vuex'

export default {
  name: 'Display',

  components: {},

  props: {
    selected: { type: Boolean, default: false },
    value: { type: Object, default: () => ({}) },
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
    "account_type": {
      "CHK": "Checking Account Ending in {last_4}",
      "SAV": "Savings Account Ending in {last_4}"
    },
    "primary": "This is your primary account",
  }
}
</i18n>
