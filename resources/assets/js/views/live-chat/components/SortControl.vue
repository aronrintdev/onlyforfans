<template>
  <b-dropdown class="filter-controls" variant="link" size="sm" right no-caret>
    <template #button-content>
      <fa-icon :icon="['fas', 'sort-amount-down']" class="fa-lg" />
    </template>

    <b-dropdown-header>
      {{ $t('header') }}
    </b-dropdown-header>

    <b-dropdown-item :active="value === 'recent'" @click="onSelect('recent')" >
      <fa-icon icon="sort-up" fixed-width class="mx-2" size="lg" />
      {{ $t('recent') }}
    </b-dropdown-item>
    <b-dropdown-item :active="value === 'oldest'" @click="onSelect('oldest')" >
      <fa-icon icon="sort-down" fixed-width class="mx-2" size="lg" />
      {{ $t('oldest') }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/SortControl.vue
 */

export default {
  name: 'SortControl',

  props: {
    value: { type: String, default: '' },
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
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "header": "Sort By",
    "recent": "Most Recent First",
    "oldest": "Oldest First"
  }
}
</i18n>
