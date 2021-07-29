<template>
  <div @click="$emit('toggled')" class="tag-clickable">
    <fa-icon
      size="lg"
      :class="filled ? 'text-danger' : 'text-secondary'"
      :icon="filled ? 'heart' : ['far', 'heart']"
    />
    <span v-if="showCount">
      ({{ count }})
    </span>
  </div>
</template>

<script>
export default {
  props: {
    count: { type: Number, default: 0 },
    filled: { type: Boolean, default: false },
    showCount: { type: Boolean, default: false },
  },
}
</script>
