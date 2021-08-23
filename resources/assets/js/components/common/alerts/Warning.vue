<template>
  <b-alert :variant="variant" :show="show">
    <b-media vertical-align="center">
      <template #aside>
        <fa-icon icon="exclamation-triangle" size="3x" fixed-width />
      </template>
      <slot>
        <div class="h4">
          <slot name="header">{{ header }}</slot>
        </div>
        <div>
          <slot name="message">{{ message }}</slot>
        </div>
      </slot>
    </b-media>
  </b-alert>
</template>

<script>
/**
 * resources/assets/js/components/common/alerts/Warning.vue
 *
 * Warning Alert
 */
export default {
  name: 'WarningAlert',

  props: {
    header: { type: String, default: '' },
    message: { type: String, default: '' },
    show: { type: Boolean, default: true },
    variant: { type: String, default: 'warning' },
  },
}
</script>
