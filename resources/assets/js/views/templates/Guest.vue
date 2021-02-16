<template>
  <div>
    <!-- Header -->
    <div class="header">
      <b-navbar variant="dark">
        <b-nav-text class="text-light">Guest Application Page</b-nav-text>
        <b-btn variant="light" href="/login" class="ml-auto">Login</b-btn>
      </b-navbar>
    </div>
    <div class="content p-3">
      <router-view />
    </div>
    <div class="footer"></div>
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

</style>