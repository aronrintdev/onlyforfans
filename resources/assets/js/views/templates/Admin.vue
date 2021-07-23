<template>
  <b-container fluid v-if="!isLoading" class="admin-container">

    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
      <a class="navbar-brand ps-3" href="index.html">AllFans Admin</a>
      <!-- Sidebar Toggle-->
      <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"></button>
    </nav>

    <b-row>
      <aside class="bg-dark col-3">
        Sidebar
        <b-nav vertical class="OFF-w-25">
          <b-nav-item active>Active</b-nav-item>
          <b-nav-item>Link</b-nav-item>
          <b-nav-item>Another Link</b-nav-item>
          <b-nav-item disabled>Disabled</b-nav-item>
        </b-nav>
      </aside>
      <main class="col-9">
        Main
      </main>
    </b-row>
  </b-container>
</template>

<script>
import Vuex from 'vuex';

//import EventUpdater from '@components/EventUpdater'
//import MainNavBar from '@components/common/MainNavbar'
//import SiteFooter from '@views/templates/SiteFooter'
//import Toaster from '@components/Toaster'

export default {
  name: 'AdminApp',
  components: {
    //EventUpdater,
    //MainNavBar,
    //SiteFooter,
    //Toaster,
  },

  props: {
    //toggleMobileAt: { type: [String, Number], default: 'md', },
    //screenSizesTypes: { type: Object, default: () => ({ xs: 0, sm: 0, md: 0, lg: 0, xl: 0 }) }
  },

  computed: {
    //...Vuex.mapState(['session_user', 'mobile', 'screenSize']),
  },

  data: () => ({
    // If full app is loading
    isLoading: false,
  }),

  methods: {
  },

  watch: {
  },

  mounted() {
  },

  created() {
  },
}
</script>

<style lang="scss">
$nav-height: 56px;
body { 

  background-color: #fff !important;

  .sb-topnav {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1030;
    padding-left: 0;
    height: $nav-height;
  }
  main {
    position: relative;
    top: $nav-height;
    height: calc(100vh - #{$nav-height});
    color: var(--dark);
  }
  aside {
    position: relative;
    top: $nav-height;
    //height: calc(100vh - 56px);
    height: calc(100vh - #{$nav-height});
    color: rgba(255, 255, 255, 0.25);
  }
}
</style>
