<template>
  <div>
    <h1>File/Media Management</h1>
    <b-pagination
      v-model="currentPage"
      :total-rows="rows"
      :per-page="perPage"
      aria-controls="mediafiles-table"
    ></b-pagination>

    <b-table hover 
      id="mediafiles-table"
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
  name: 'MediafileManagement',

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
      { key: 'created_at', label: 'Created', },
    ],
  }),

  methods: {
    async getUsers(ctx) {
      const params = `?page=${ctx.currentPage}&take=${ctx.perPage}`
      try {
        const response = await axios.get( this.$apiRoute('diskmediafiles.index')+params )
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
