<template>
  <div>
    <h1>Content Management</h1>
    <b-tabs>
      <b-tab title="Tips" active>
      </b-tab>
      <b-tab title="Likes" active>
      </b-tab>
      <b-tab title="Favorites" active>
      </b-tab>
      <b-tab active title="Reported" active>
        <ListContentflag />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListContentflag from './ListContentflag'

export default {

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListContentflag,
  },

  name: 'ContentManagement',
}
</script>

<style lang="scss" scoped>
</style>
