<template>
  <div>
    <h1>User Management</h1>
    <b-tabs>
      <b-tab title="List" lazy active>
        <ListUsers />
      </b-tab>
      <b-tab title="Roles" lazy>
        <Roles />
      </b-tab>
      <b-tab title="Permissions" lazy>
        <Permissions />
      </b-tab>
      <b-tab title="Identity Verify" lazy>
        <ListIdentityVerify />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListUsers from './ListUsers'
import ListIdentityVerify from './ListIdentityVerify'
import Permissions from './Permissions.vue'
import Roles from './Roles.vue'

export default {
  name: 'UserManagement',

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListUsers,
    ListIdentityVerify,
    Permissions,
    Roles,
  },
}
</script>

<style lang="scss" scoped>
</style>
