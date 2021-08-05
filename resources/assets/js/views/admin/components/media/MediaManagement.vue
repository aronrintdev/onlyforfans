<template>
  <div>
    <h1>Media Management</h1>
    <b-tabs>
      <b-tab title="Mediafiles" lazy active>
        <ListMediafiles />
      </b-tab>
      <b-tab title="Vaults" >
        TBD
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListMediafiles from './ListMediafiles'

export default {

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListMediafiles,
  },

  name: 'MediaManagement',
}
</script>

<style lang="scss" scoped>
</style>
