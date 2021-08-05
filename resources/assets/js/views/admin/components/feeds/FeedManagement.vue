<template>
  <div>
    <h1>Feed Management</h1>
    <b-tabs>
      <b-tab title="Posts" active>
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

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListPosts,
  },

  name: 'FeedManagement',
}
</script>

<style lang="scss" scoped>
</style>
