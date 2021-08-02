<template>
  <div v-if="loading" class="overlay d-flex flex-column justify-content-around align-items-center">
    <slot>
      <div class="d-flex flex-column align-items-center">
        <fa-icon icon="spinner" spin size="2x" fixed-width class="mb-3" />
        <div class="text-center" v-text="text || $t('Loading')" />
      </div>

    </slot>
  </div>
</template>

<script>
export default {
  name: 'LoadingOverlay',

  props: {
    loading: { type: Boolean, default: false},
    text: { type: String, default: null },
  },
}
</script>

<style lang="scss" scoped>
.overlay {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  color: var(--dark, #000000);
  background-color: rgba(255, 255, 255, 0.5);
  z-index: 100;
  cursor: wait;
}
</style>

<i18n lang="json5">
{
  "en": {
    "Loading": "Loading"
  }
}
</i18n>
