<template>
  <div class="app d-flex flex-column">
    <!-- Header -->
    <div class="header">
      <MainNavBar />
    </div>
    <div class="content p-3  flex-grow-1">
      <router-view />
    </div>
    <div class="footer">
      <b-navbar variant="dark">
        <b-nav-text class="text-light mx-auto">
          &copy; {{ $DateTime().year }} All Fans. All rights reserved.
        </b-nav-text>
      </b-navbar>
    </div>
  </div>
</template>

<script>
import Vuex from 'vuex';
import MainNavBar from '@components/common/MainNavbar'

export default {
  components: {
    MainNavBar,
  },
  computed: {
    ...Vuex.mapState(['session_user']),
  },

  data: () => ({
    onlineMonitor: null,
  }),

  methods: {
    ...Vuex.mapActions(['getMe']),
    startOnlineMonitor() {
      if (this.session_user) {
        this.onlineMonitor = this.$echo.join(`user.status.${this.session_user.id}`)
      }
    },
  },

  watch: {
    session_user(value) {
      if (value) {
        this.startOnlineMonitor()
      }
    },
  },

  mounted() {
    if (!this.session_user) {
      this.getMe()
    }
  },
}
</script>
