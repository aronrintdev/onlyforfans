<template>
  <div class="container-fluid" :class="mobile ? 'px-0' : ''">
    <!-- Mobile view -->
    <section v-if="mobile && !isMessages" class="mobile-settings-content" :class="{ 'focus-main': focusMain }">
      <!-- <transition :name="focusMain ? 'slide-right' : 'slide-left'"> -->
        <div class="sidebar px-3 pb-3" key="sidebar">
          <slot name="sidebar"></slot>
        </div>
        <div class="main px-3 pb-3" key="main">
          <b-btn variant="link" size="lg" class="back-btn" @click="$emit('back')">
            <fa-icon icon="arrow-left" size="lg" />
          </b-btn>

          <div class="settings-page">
            <slot></slot>
          </div>
        </div>
      <!-- </transition> -->
    </section>

    <!-- Messages Mobile view -->
    <!-- FIXME: This is one of the dirtiest 💩 fixes I've done as I don't have another choice -->
    <section v-else-if="mobile && isMessages" class="mobile h-100" :class="{ 'focus-main': focusMain }">
      <div class="sidebar px-3 pb-3" key="sidebar">
        <slot name="sidebar"></slot>
      </div>
      <div class="main px-3 pb-3" key="main">
        <slot></slot>
      </div>
    </section>

    <!-- Non mobile view -->
    <section v-else class="d-flex flex-nowrap h-100 w-100">
      <aside class="sidebar h-100 p-3 mr-3" :class="{'border-class': hasBorder}">
        <slot name="sidebar"></slot>
      </aside>
      <main class="main flex-fill h-100 pt-3">
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

    removeMobileMainNavTop: { type: Boolean, default: false },

    isMessages: { type: Boolean, default: false },

    hasBorder: { type: Boolean, default: false },
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
    max-height: 100%;
    overflow-y: auto;

    .header {
      position: sticky;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      background-color: white;
      display: flex;
      align-items: center;
    }
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
  width: 20rem;
  min-width: 20rem;
}
.border-class {
  border-right: solid 1px #dfdfdf;
}
.main {
  max-height: 100%;
  min-width: 20rem;
}

.mobile-settings-content {
  overflow-x: hidden;
  .main {
    position: relative;
    transform: translate3d(100%, 0, 0);
  }

  .back-btn {
    display: none;
  }

  &.focus-main {
    .back-btn {
      display: block;
      padding-left: 2px;
      padding-right: 2px;
    }
    .sidebar {
      transform: translate3d(-100%, 0, 0);
      position: absolute;
    }
    .main{
      transform: translate3d(0, 0, 0);
      position: relative;
    }
  }
}

</style>

<style lang="scss">
#view-settings {
  .border-class {
    border: none;
  }
}

@media (max-width: 576px) {
  #view-settings {
    .container-fluid {
      overflow-x: hidden;
    }
  }

  .settings-page {
    .card {
      border: none;
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "back": "Back"
  }
}
</i18n>
