<template>
  <div>
    <ResourceEditor v-model="role" model-name="Role" :protected-fields="[ 'guard_name' ]"/>

    <!-- Permissions -->

    <!-- Add Permissions -->
  </div>
</template>

<script>
/**
 * View / Edit specific role
 */

import ResourceEditor from '../../common/ResourceEditor'

export default {
  components: {
    ResourceEditor,
  },
  props: {
    roleId: {type: [Number, String]},
  },
  data: () => ({
    role: null,
    state: 'loading', // loading | viewing | editing | error
  }),
  methods: {
    load() {
      if (this.roleId) {
        this.state = 'loading'
        this.axios.get(`admin/role/${this.roleId}`)
        .then(response => {
          if (response.statusText === 'OK' ) {
            this.role = response.data
          } else {
            this.state = 'error'
          }
        })
        .catch(error => {
          this.$log.error(error)
          this.state = 'error'
        })
      }
    }
  },
  watch: {
    roleId() {
      this.load()
    }
  },
  mounted() {
    this.load()
  },
}
</script>

<style lang="scss" scoped>

</style>