<template>
  <div>
    <h1>User Management</h1>
    <b-tabs>
      <b-tab title="List" active>
        <ListUsers />
      </b-tab>
      <b-tab title="Roles">
        <Roles />
      </b-tab>
      <b-tab title="Permissions">
        <Permissions />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListUsers from './ListUsers'
import Permissions from './Permissions.vue'
import Roles from './Roles.vue'
//import Permissions from '@components/admin/security/Permissions'
//import Roles from '@components/admin/security/Roles'

export default {
  name: 'UserManagement',

  props: {},

  computed: {
  },

  data: () => ({
  }),

  methods: {
  },

  watchers: {},

  created() {},

  components: {
    ListUsers,
    Permissions,
    Roles,
  },
}
</script>

<style lang="scss" scoped>
</style>
