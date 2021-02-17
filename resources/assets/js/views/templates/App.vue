<template>
  <div class="app d-flex flex-column">
    <!-- Header -->
    <div class="header">
      <b-navbar variant="dark">
        <b-nav-text class="text-light">All Fans Application Page</b-nav-text>
        <b-btn variant="light" class="ml-auto" @click="logout">Logout</b-btn>
      </b-navbar>
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
export default {
  computed: {
    ...Vuex.mapState(['session_user']),
  },
  methods: {
    ...Vuex.mapActions(['getMe']),
    logout() {
      this.axios.post('/logout')
      .then(() => {
        window.location = '/login'
      })
    },
  },
  mounted() {
    if (!this.session_user) {
      this.getMe()
    }
  },
}
</script>
