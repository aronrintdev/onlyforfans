<template>
  <div
    class="form-input-append-control cursor-pointer"
    :title="$t('clear')"
    @click="onClick"
  >
    <fa-icon icon="times" fixed-width />
  </div>
</template>

<script>
/**
 * resources/assets/js/components/forms/elements/FormInputClearButton.vue
 */
export default {
  name: 'FormInputClearButton',
  props: {},
  methods: {
    onClick() {
      this.$emit('clear')
    },
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "clear": "Clear"
  }
}
</i18n>
