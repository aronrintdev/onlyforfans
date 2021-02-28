<template>
  <div ref="base" class="wrapper" :style="wrapperCss">
    <div class="inner" :style="innerCss">
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
  },

  computed: {
    wrapperCss() {
      if (this.forceOpen) {
        return ``
      }
      return `height: ${ this.maxHeight + this.hidden }px; transition: height var(--transition-rate, 0.25s) var(--transition-easing, ease);`
    },
    innerCss() {
      return `top: ${this.hidden}px; transition: top var(--transition-rate, 0.25s) var(--transition-easing, ease);`
    },
  },

  data: () => ({
    hidden: 0,
    maxHeight: 0,
    lastYPosition: window.scrollY || window.pageYOffset,
    forceOpen: false,
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
      clearTimeout(this.isScrolling)
      const position = window.scrollY || window.pageYOffset
      const diff = this.lastYPosition - position
      // At top of screen
      if (position === 0) {
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
    },
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
