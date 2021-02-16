<template>
  <div class="app d-flex flex-column">
    <!-- Header -->
    <div class="header">
      <b-navbar variant="dark">
        <b-nav-text class="text-light">Guest Application Page</b-nav-text>
        <b-btn variant="light" href="/login" class="ml-auto">Login</b-btn>
      </b-navbar>
    </div>
    <div class="content d-flex p-3 flex-grow-1">
      <router-view />
    </div>
    <div class="footer">
      <b-navbar variant="dark">
        <b-nav-text class="text-light">Footer</b-nav-text>
      </b-navbar>
    </div>
  </div>
</template>

<script>
/**
 * View Template for guest pages
 */
export default {
  
}
</script>

<style lang="scss" scoped>
.content {
  min-height: 100%;
}
</style>
