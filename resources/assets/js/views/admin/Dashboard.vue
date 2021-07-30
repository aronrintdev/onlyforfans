<template>
  <div>
    <b-row>
      <aside class="bg-dark col-sm-2">
        <MajorNav />
      </aside>

      <main class="col-sm-10">
        <router-view />
      </main>

    </b-row>
  </div>
</template>

<script>
import Vuex from 'vuex'
import MajorNav from '@views/admin/components/common/MajorNav'
//import MainNavbar from '@views/admin/components/common/MainNavbar'
//import EventUpdater from '@components/EventUpdater'
//import SiteFooter from '@views/templates/SiteFooter'
//import Toaster from '@components/Toaster'

export default {
  name: 'Dashboard',

  components: {},

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

  watchers: {},

  created() {},

  components: {
    MajorNav,
    //MainNavbar,
    //EventUpdater,
    //SiteFooter,
    //Toaster,
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
