<template>
  <div>
    <AdminTable :fields="fields" indexRouteName="posts.index" />
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import AdminTable from '@views/admin/components/common/AdminTable'

export default {
  name: 'PostManagement',

  props: {},

  computed: { },

  data: () => ({
    fields: [
      { key: 'id', label: 'ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'description', label: 'Content' },
      { key: 'created_at', label: 'Created', formatter: v => Vue.options.filters.niceDate(v) },
    ],
  }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    AdminTable,
  },
}
</script>

<style lang="scss" scoped>
</style>
