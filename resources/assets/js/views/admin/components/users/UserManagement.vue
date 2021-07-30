<template>
  <div>
    <h1>User Management</h1>
    <b-pagination
      v-model="currentPage"
      :total-rows="rows"
      :per-page="perPage"
      aria-controls="users-table"
    ></b-pagination>

    <b-table hover 
      id="users-table"
      :items="getUsers"
      :per-page="perPage"
      :current-page="currentPage"
      :fields="fields"
      small
    >
      <template #cell(id)="data">
        <span class="OFF-text-info">{{ data.item.id | niceGuid }}</span>
      </template>
      <template #cell(timeline)="data">
        <span class="OFF-text-info">{{ data.item.timeline.id | niceGuid }}</span>
      </template>
      <template #cell(created_at)="data">
        <span class="OFF-text-info">{{ data.item.created_at | niceDate }}</span>
      </template>
    </b-table>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

export default {
  name: 'UserManagement',

  props: {},

  computed: {
  },

  data: () => ({
    //items: [],
    currentPage: 1,
    rows: null,
    perPage: 10,
    fields: [
      { key: 'id', label: 'id', },
      { key: 'email', label: 'email', },
      { key: 'username', label: 'username', },
      { key: 'firstname', label: 'firstname', },
      { key: 'lastname', label: 'lastname', },
      { key: 'is_verified', label: 'Identity Verified?', },
      { key: 'timeline', label: 'Timeline', },
      { key: 'created_at', label: 'Joined', },
    ],
  }),

  methods: {
    async getUsers(ctx) {
      const params = `?page=${ctx.currentPage}&take=${ctx.perPage}`
      try {
        const response = await axios.get( this.$apiRoute('users.index')+params )
        this.rows = response.data.meta.total
        return response.data.data
      } catch (e) {
        throw e
        return []
      }
    }
  },

  watchers: {},

  created() {
  },

  components: {},
}
</script>

<style lang="scss" scoped>
</style>
