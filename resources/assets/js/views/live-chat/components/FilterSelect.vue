<template>
  <b-badge
    :variant="selected ? selectedVariant : variant"
    :active="selected"
    :pill="pill"
    href="#"
    class="filter-select text-nowrap cursor-pointer select-none"
    @click="onClick"
    :style="{ fontSize: '90%' }"
  >
    <!-- <fa-icon v-if="selected && !noSelectedIcon" :icon="selectedIcon" fixed-width /> -->
    <slot>
      {{ label }}
    </slot>
  </b-badge>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/FilterSelect.vue
 */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'FilterSelect',

  model: {
    'prop': 'selected',
    'event': 'change',
  },

  props: {
    label:           { type: String, default: '' },
    noSelectedIcon:  { type: Boolean, default: false },
    pill:            { type: Boolean, default: false },
    selected:        { type: Boolean, default: false },
    selectedVariant: { type: String, default: 'info' },
    selectedIcon:    { type: FontAwesomeIcon.props.icon.type, default: 'check' },
    variant:         { type: String, default: 'outline-info' },
  },

  methods: {
    onClick() {
      if (this.selected) {
        this.$emit('change', false)
        this.$emit('unselected')
      } else {
        this.$emit('change', true)
        this.$emit('selected')
      }
    }
  },

}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
