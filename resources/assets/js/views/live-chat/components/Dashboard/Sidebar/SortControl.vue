<template>
  <b-dropdown class="filter-controls" variant="link" size="sm" left no-caret>
    <template #button-content>
      <span class="sort-button">Sort</span>
      <fa-icon :icon="asc ? ['fas', 'caret-up'] : ['fas', 'caret-down']" class="fa-lg" />
    </template>

    <b-dropdown-header>
      {{ $t('header') }}
    </b-dropdown-header>

    <b-dropdown-item :active="value === 'recent'" @click="onSelect('recent')" >
      <span class="ml-3">{{ $t('date') }}</span>
    </b-dropdown-item>
    <b-dropdown-item :active="value === 'unread'" @click="onSelect('unread')" >
      <span class="ml-3">{{ $t('unread') }}</span>
    </b-dropdown-item>
    <b-dropdown-item :active="value === 'unreadWithTips'" @click="onSelect('unreadWithTips')" >
      <span class="ml-3">{{ $t('unreadWithTips') }}</span>
    </b-dropdown-item>
    <b-dropdown-item :active="value === 'online'" @click="onSelect('online')" >
      <span class="ml-3">{{ $t('online') }}</span>
    </b-dropdown-item>
    <b-dropdown-item :active="value === 'amountSpent'" @click="onSelect('amountSpent')" >
      <span class="ml-3">{{ $t('amountSpent') }}</span>
    </b-dropdown-item>
    <b-dropdown-divider></b-dropdown-divider>
    <b-dropdown-item :active="asc" @click="$emit('sortBy', true)" >
      <fa-icon icon="sort-up" fixed-width class="mx-2" size="lg" />
      {{ $t('ascending') }}
    </b-dropdown-item>
    <b-dropdown-item :active="!asc" @click="$emit('sortBy', false)" >
      <fa-icon icon="sort-down" fixed-width class="mx-2" size="lg" />
      {{ $t('descending') }}
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
    asc: { type: Boolean, default: false },
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
.sort-button {
  font-size: 16px;
  font-weight: 500;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "header": "Sort By",
    "unread": "Unread",
    "unreadWithTips": "Unread with Tips",
    "date": "Date",
    "online": "Currently Online",
    "amountSpent": "Total Amount Spent",
    "ascending": "Ascending",
    "descending": "Descending",
  }
}
</i18n>
