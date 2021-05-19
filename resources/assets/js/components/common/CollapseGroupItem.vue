<template>
  <div>
    <b-list-group-item class="cursor-pointer d-flex align-items-center pl-2" @click="open = !open">
      <fa-icon icon="caret-right" fixed-width class="open-indicator mr-2" :class="{ open: open }" />
      <slot name="parent"></slot>
    </b-list-group-item>
    <b-collapse v-model="open">
      <b-list-group class="ml-3 child-group">
        <slot></slot>
      </b-list-group>
    </b-collapse>

  </div>
</template>

<script>
export default {
  name: 'CollapseGroup',

  data: () => ({
    open: false,
  }),

  watch: {
    open(val) {
      if (val) {
        this.$emit('opening')
      } else {
        this.$emit('closing')
      }
    }
  },

}
</script>

<style lang="scss" scoped>

.open-indicator {
  transition: transform 0.2s ease;
  &.open {
    transform: rotate(90deg);
  }
}
.child-group {
  & > :first-child {
    border-top: 0;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
  }
  & > :last-child {
    margin-bottom: 1rem;
  }
}
</style>
