<template>
  <b-container fluid v-if="!isLoading" class="admin-container">

    <MainNavbar class="header" />

    <b-row>

      <aside class="bg-dark col-sm-2">
        <MajorNav />
      </aside>

      <main class="col-sm-10">
        <router-view />
        <!-- HERE -->
      </main>

    </b-row>

  </b-container>
</template>

<script>
import Vuex from 'vuex';

import MainNavbar from '@components/admin/common/MainNavbar'
import MajorNav from '@components/admin/common/MajorNav'
//import EventUpdater from '@components/EventUpdater'
//import SiteFooter from '@views/templates/SiteFooter'
//import Toaster from '@components/Toaster'

export default {
  name: 'AdminApp',
  components: {
    MainNavbar,
    MajorNav,
    //EventUpdater,
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
    min-height: calc(100vh - #{$nav-height});
    color: var(--dark);
  }
  aside {
    position: relative;
    top: $nav-height;
    //height: calc(100vh - 56px);
    min-height: calc(100vh - #{$nav-height});
    color: rgba(255, 255, 255, 0.25);
  }
}
</style>
