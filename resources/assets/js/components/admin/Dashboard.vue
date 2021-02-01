<template>
  <div>
    <div class="h1">Admin Dashboard</div>
    <b-card>
      <Roles />
    </b-card>
  </div>
</template>

<script>
import Permissions from './security/Permissions.vue'
import Roles from './security/Roles.vue'

/**
 * Main Admin Dashboard View
 */
export default {
  name: 'dashboard',
  components: {
    Permissions,
    Roles,
  },
}
</script>

<style lang="scss" scoped>

</style>
