<template>
  <div class="container-fluid">

    <!-- Mobile view -->
    <section v-if="mobile" class="mobile h-100" :class="{ 'focus-main': focusMain }">
      <!-- <transition :name="focusMain ? 'slide-right' : 'slide-left'"> -->
        <div class="sidebar" key="sidebar">
          <slot name="sidebar"></slot>
        </div>
        <div class="main" key="main">
          <slot></slot>
        </div>
      <!-- </transition> -->
    </section>

    <!-- Non mobile view -->
    <section v-else class="d-flex flex-nowrap h-100 w-100">
      <aside class="sidebar mr-3">
        <slot name="sidebar"></slot>
      </aside>
      <main class="main flex-fill h-100 py-3">
        <slot></slot>
      </main>
    </section>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/layouts/WithSidebar.vue
 */
import Vuex from 'vuex'

export default {
  name: 'WithSidebar',

  components: {},

  props: {
    // Switcher for mobile view
    focusMain: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState(['mobile', 'screenSize']),
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.mobile {
  display: block;
  position: relative;
  overflow-x: hidden;
  overflow-y: hidden;
  height: 100%;
  max-height: 100vh;
  .sidebar, .main {
    transition: transform .3s ease;
    position: relative;
    overflow-x: hidden;
    width: 100vw;
    max-width: 100vw;
    height: 100%;
    max-height: 100vh;
  }

  .sidebar {
    transform: translate3d(0, 0, 0);
  }
  .main {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate3d(100%, 0, 0);
  }
  &.focus-main {
    .sidebar {
      transform: translate3d(-100%, 0, 0);
    }
    .main{
      transform: translate3d(0, 0, 0);
    }
  }
}

.sidebar {
  width: 25rem;
  min-width: 20rem;
}
.main {
  max-height: 100%;
  min-width: 20rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
