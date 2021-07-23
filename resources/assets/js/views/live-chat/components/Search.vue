<template>
  <div class="search d-flex align-items-center mb-2 w-100">
    <label for="create-thread-search" v-text="label" class="text-nowrap mr-2 mb-0" />
    <b-input
      ref="searchInput"
      id="create-thread-search"
      :value="value"
      class="flex-grow-1"
      @input="value => $emit('input', value)"
    />
    <b-btn variant="link" @click="onIconClick">
      <fa-icon :icon="value === '' ? 'search' : 'times'" size="lg" />
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

  methods: {
    onIconClick() {
      if (this.value === '') {
        // Focus the search box
        this.$refs.searchInput.$el.focus()
      } else {
        // Clear search term
        this.$emit('input', '')
      }
    },
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
