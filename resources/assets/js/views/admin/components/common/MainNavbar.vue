<template>
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

    <b-navbar-nav class="">
      <router-link :to="{ name: 'home', params: {} }" class="navbar-brand pl-3">AllFans Admin</router-link>
    </b-navbar-nav>

    <b-navbar-nav class="ml-auto">
      <b-link href="/" class="navbar-brand pl-3">Main Site</b-link>
    </b-navbar-nav>

    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"></button>
  </nav>
</template>

<script>
export default {
}
</script>

<style lang="scss" scoped>
</style>
