<template>
  <div>
    <b-badge
      v-for="(item, index) in options"
      :key="item.key"
      :variant="selected === item.key ? selectedVariant : variant"
      :active="selected === item.key"
      pill
      href="#"
      class="filter-select text-nowrap cursor-pointer select-none font-size-small mr-2"
      @click="() => onSelect(index)"
    >
      <slot>
        {{ item.label }}
      </slot>
    </b-badge>
    </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/RangeSelector.vue
 */
import Vuex from 'vuex'

export default {
  name: 'RangeSelector',

  components: {},
  props: {
    variant: { type: String, default: 'light' },
    selectedVariant: { type: String, default: 'secondary' },
  },

  computed: {
    options() {
      return [
        {
          key: 'past7days',
          label: this.$t('labels.past7days'),
          ago: 7,
          ago_unit: 'day',
        }, {
          key: 'past30days',
          label: this.$t('labels.past30days'),
          ago: 30,
          ago_unit: 'day',
        }, {
          key: 'past4weeks',
          label: this.$t('labels.past4weeks'),
          ago: 4,
          ago_unit: 'week',
        }, {
          key: 'past6months',
          label: this.$t('labels.past6months'),
          ago: 6,
          ago_unit: 'month',
        }, {
          key: 'past12months',
          label: this.$t('labels.past12months'),
          ago: 12,
          ago_unit: 'month',
        },
      ]
    },
  },

  data: () => ({
    selected: 'past7days',
  }),

  methods: {
    onSelect(index) {
      this.selected = this.options[index].key
      this.$emit('select', this.options[index])
    }
  },

  watch: {},

  mounted() {
    this.$emit('select', this.options[_.findIndex(this.options, o => o.key === this.selected)])
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "labels": {
      "past7days": "Past 7 Days",
      "past30days": "Past 30 Days",
      "past4weeks": "Past 4 Weeks",
      "past6months": "Past 6 Months",
      "past12months": "Past 12 Months",
    }
  }
}
</i18n>
