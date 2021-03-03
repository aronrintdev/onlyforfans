<template>
  <div ref="base" class="wrapper" :style="wrapperCss">
    <div class="inner h-100" :style="innerCss">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'

export default {
  props: {
    sensitivity: { type: Number, default: 1, },
    openThreshold: { type: Number, default: 20 },
    /**
     * Percentage at which to fully close collapse
     */
    closeThreshold: { type: Number, default: 0.90 },
    fullOpen: { type: Boolean, default: false },
    fullOpenHeight: { type: Number, default: 500 }
  },

  computed: {
    wrapperCss() {
      if (this.forceOpen) {
        return ``
      }
      if (this.fullOpen) {
        return `height: ${ this.fullOpenHeight }px; transition: height ${this.transition};`
      }
      return `height: ${ this.maxHeight + this.hidden }px; transition: height ${this.transition};`
    },
    innerCss() {
      if(this.fullOpen) {
        return `height: 100%; top: ${this.hidden}px; transition: top ${this.transition};`
      }
      return `top: ${this.hidden}px; transition: top ${this.transition};`
    },
  },

  data: () => ({
    hidden: 0,
    maxHeight: 0,
    lastYPosition: window.scrollY || window.pageYOffset,
    forceOpen: false,
    transition: 'var(--transition-rate, 0.25s) var(--transition-easing, ease)',
  }),

  methods: {
    calculateMaxHeight() {
      this.forceOpen = true
      /**
       * Range from -maxHeight to 0, 0 is fully open
       */
      this.hidden = 0
      // this.tweenedScrollCollapseTop = 0
      this.$nextTick(() => {
        this.maxHeight = this.$refs['base'].clientHeight
        this.forceOpen = false
      })
    },
    onScroll() {
      const position = window.scrollY || window.pageYOffset
      const diff = this.lastYPosition - position
      if (this.fullOpen) {
        this.hidden = 0
      } else if (position === 0) { // At top of screen
        this.hidden = 0
      } else if (diff > this.openThreshold) {
        this.hidden = 0
      } else {
        this.hidden = _.clamp(this.hidden + (diff * this.sensitivity), 0 - this.maxHeight, 0)
        // If scrolling down and hidden amount is greater that threshold, then fully close
        if ( diff < 0 && this.hidden / (0 - this.maxHeight) >= this.closeThreshold ) {
          this.hidden = 0 - this.maxHeight
        }
      }
      this.lastYPosition = position
      this.$emit('scroll')
    },
  },

  watch: {
    fullOpen(value) {
      if (value) {
        this.hidden = 0
      }
    }
  },

  created() {
    window.addEventListener('scroll', this.onScroll)
  },
  destroyed() {
    window.removeEventListener('scroll', this.onScroll)
  },
  mounted() {
    this.calculateMaxHeight()
  },
}
</script>

<style lang="scss" scoped>
.wrapper {
  overflow-y: hidden;
  & > .inner {
    position: relative;
  }
}
</style>
