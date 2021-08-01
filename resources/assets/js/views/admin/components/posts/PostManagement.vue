<template>
  <div>
    <h1>Post Management</h1>
    <b-tabs>
      <b-tab title="List" active>
        <ListPosts />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListPosts from './ListPosts'

export default {
  name: 'PostManagement',

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListPosts,
  },
}
</script>

<style lang="scss" scoped>
</style>
