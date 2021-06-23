<template>
  <div class="search d-flex align-items-center mb-2 w-100">
    <label for="create-thread-search" v-text="label" class="text-nowrap mr-2 mb-0" />
    <b-input ref="searchInput" id="create-thread-search" :value="value" @input="value => $emit('input', value)" class="flex-grow-1" />
    <b-btn variant="link" @click="$refs.searchInput.$el.focus()">
      <fa-icon icon="search" size="lg" />
    </b-btn>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Search.vue
 */

export default {
  name: 'Search',

  props: {
    label: { type: String, default: 'Search' },
    value: { type: String, default: '' },
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
