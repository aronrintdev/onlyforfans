<template>
  <b-dropdown class="filter-controls" variant="link" size="sm" left no-caret>
    <template #button-content>
      <span class="filter-button">View</span>
      <fa-icon :icon="['fas', 'caret-down']" class="fa-lg" />
    </template>

    <b-dropdown-header>
      {{ $t('header') }}
    </b-dropdown-header>

    <b-dropdown-item v-for="filter in filters" :active="value === filter.value" @click="onSelect(filter.value)" :key="filter.value">
      <span class="ml-3">{{ filter.text }}</span>
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/FilterControl.vue
 */

export default {
  name: 'FilterControl',

  props: {
    value: { type: String, default: '' },
    filters: { type: Array, default: () => ([]) },
  },

  methods: {
    onSelect(value) {
      this.$emit('input', value)
    },
  },
}
</script>

<style lang="scss" scoped>
.filter-controls {
  ::v-deep .dropdown-item {
    padding-left: 0;
  }
}
.filter-button {
  font-size: 16px;
  font-weight: 500;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "header": "View By",
  }
}
</i18n>