<template>
  <div class="status-dot" :class="`bg-${variant}`" :style="{ height: sizing, width: sizing }" />
</template>

<script>
/**
 * resources/assets/js/components/user/StatusDot.vue
 */
import Vuex from 'vuex'

export default {
  name: 'StatusDot',

  components: {},

  props: {
    size: { type: String, default: 'sm' },
    variant: { type: String, default: 'muted' },
  },

  computed: {
    sizing() {
      const baseSize = 1
      switch(this.size) {
        case 'xs': return `${ baseSize * 0.75 }rem`
        case 'sm': default: return `${ baseSize * 1 }rem`
        case 'md':  return `${ baseSize * 1.5 }rem`
        case 'lg': return `${ baseSize * 3   }rem`
      }
    }
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
$spacer: 1rem;
.status-dot {
  border-radius: 50%;
  width: $spacer;
  height: $spacer;

  padding: 5px;
  background-color: var(--gra-dark, #343a40);

  box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
  z-index: 2;

  border: 2px var(--light, #343a40) solid;

  .dot {
    border-radius: 50%;
    z-index: 3;
  }
}
.bg-muted {
  background-color: #d8d8d8 !important;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
