<template>
  <div class="container-xl" id="view-staff_invitations_accpet">
  </div>
</template>

<script>

export default {
  mounted() {
    const { is_new, email } = this.$route.query;
    // Not logged in user
    if (is_new === 'true') {
      this.$router.push({ name: 'register', params: { redirect: this.$route.fullPath, email } })
    } else {
      this.$router.push({ name: 'login', params: { redirect: this.$route.fullPath, email } })
    }
  },
}
</script>

<style lang="scss" scoped>

</style>
