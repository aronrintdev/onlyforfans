<template>
  <b-alert show variant="danger">
    <b-media>
      <template #aside>
        <fa-icon :icon="icon" size="2x" class="mr-2" />
      </template>
      <div v-if="title" class="h4" v-text="title" />
      <div v-if="$slots.title" class="h4">
        <slot name="title"></slot>
      </div>

      <p>
        <slot>An error has occurred.</slot>
      </p>
    </b-media>
  </b-alert>
</template>

<script>
/**
 * Quick Error Alert
 */
export default {
  props: {
    title: { type: String },
    icon: { type: [String, Array], default: 'exclamation-triangle' },
  },
}
</script>
