<template>
  <div class="wrapper" @click="restart">
    <div class="d-flex h-100 justify-content-center align-items-center">
      <div ref="icon" >
        <fa-layers class="fa-5x">
          <fa-icon ref="outerCircle" :icon="['fas', 'circle']" transform="grow-4" class="text-white" />
          <fa-icon ref="innerCircle" :icon="['fas', 'circle']" class="text-success" />
          <fa-icon ref="check" icon="check" transform="shrink-6" class="text-white" />
        </fa-layers>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/flair/ConfirmationCheckAnime.vue
 * Spin and grow animation of check mark to indicate confirmation of an action
 */
import anime from 'animejs'

export default {
  name: 'ConfirmationCheckAnime',

  components: {},

  props: {
    duration: { type: Number, default: 3000 },
  },

  computed: {},

  data: () => ({
    // breathing: null,
    outerCircle: null,
    innerCircle: null,
    check: null,
  }),

  mounted() {
    this.setup()
    this.$nextTick(() => this.play())
  },

  methods: {

    play() {
      this.outerCircle.play()
      this.innerCircle.play()
      this.check.play()
      // this.breathing.play()
    },

    restart() {
      this.outerCircle.restart()
      this.innerCircle.restart()
      this.check.restart()
      // this.breathing.restart()
    },

    setup() {
      const easingFunction = 'easeInOutElastic'
      // const easingFunction = 'easeInOutBack'

      // outerCircle
      this.outerCircle = anime({
        targets: this.$refs.outerCircle.$el,
        loop: false,
        easing: easingFunction,
        scale: [
          { value: 1, duration: 0 },
          { value: 1.25, duration: 1000, },
        ],
        filter: [
          { value: 'drop-shadow( 0 0 0 white)', duration: 0 },
          { value: 'drop-shadow( 0 0 1.5rem white)', duration: 1000, delay: 0, easing: 'easeInOutQuint' },
        ],

      })

      // Inner Circle
      this.innerCircle = anime({
        targets: this.$refs.innerCircle.$el,
        loop: false,
        easing: easingFunction,
        keyframes: [
          { scale: 1, duration: 0 },
          { scale: 1.25, duration: 1000, delay: 50 },
        ],
      })

      // Check
      this.check = anime({
        targets: this.$refs.check.$el,
        loop: false,
        easing: easingFunction,
        rotate: [
          { value: -135, duration: 0 },
          { value: 0, duration: 1400, easing: 'easeOutElastic' },
        ],
        scale: [
          { value: 1.25, duration: 500, delay: 300, easing: easingFunction, },
        ],
      })

      // Overall Icon breathing
      // this.breathing = anime({
      //   targets: this.$refs.icon,
      //   loop: true,
      //   direction: 'alternate',
      //   easing: 'easeInOutSine',
      //   // delay: 1000,
      //   scale: [
      //     // { value: 1, duration: 0 },
      //     { value: 1.05, duration: 1000 },
      //   ]
      // })

    },

  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.wrapper {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;

  background-color: rgba(255, 255, 255, 0.5);
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
