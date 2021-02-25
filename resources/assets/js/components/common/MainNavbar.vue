<template>
  <b-navbar toggleable="md" variant="light" class="bg-white OFF-fixed-top">
    <b-navbar-brand :to="{ name: 'index' }" class="navbar-brand mr-5">
      All Fans
    </b-navbar-brand>
    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav>
        <SearchBar />
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <ProfileMenu />
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>

<script>
import Vuex from 'vuex';
import ProfileMenu from './navbar/ProfileMenu'
import SearchBar from './navbar/SearchBar'

export default {
  components: {
    ProfileMenu,
    SearchBar,
  },

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),
  },

  data: () => ({
  }),

  created() {
  },

  methods: {
  },
}
</script>

<style scoped>
</style>

