<template>
  <div>
    <!-- Header -->
    <div class="header">
      <b-navbar variant="dark">
        <b-nav-text class="text-light">All Fans Application Page</b-nav-text>
        <b-btn variant="light" class="ml-auto" @click="logout">Logout</b-btn>
      </b-navbar>
    </div>
    <div class="content p-3">
      <router-view />
    </div>
    <div class="footer"></div>
  </div>
</template>

<script>
export default {
  methods: {
    logout() {
      this.axios.post('/logout')
      .then(() => {
        window.location = '/login'
      })
    }
  },
}
</script>

<style lang="scss" scoped>

</style>