<template>
  <div @click="$emit('toggled')" class="tag-clickable">
    <b-icon
      :icon="filled ? 'heart-fill' : 'heart'"
      :variant="filled ? 'danger' : 'default'"
      font-scale="1"
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
