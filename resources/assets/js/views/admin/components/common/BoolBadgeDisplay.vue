<template>
  <b-badge :variant="variant">
    <fa-icon :icon="value ? 'check' : 'times'" size="lg" fixed-width />
    <span><slot></slot></span>
  </b-badge>
</template>

<script>
/**
 * resources/assets/js/views/admin/components/common/BoolBadgeDisplay.vue
 *
 * Quick Boolean Display as Badge
 */

export default {
  name: 'BoolBadgeDisplay',

  props: {
    value: { type: Boolean, default: false },
  },

  computed: {
    icon() {
      return this.value ? ['fas', 'check'] : ['fas', 'times']
    },
    variant() {
      return this.value ? 'success' : 'danger'
    },
  },
}
</script>
