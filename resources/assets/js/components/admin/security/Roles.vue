<template>
  <b-card no-body>
    <template #header>
      <div class="d-flex justify-content-between">
        <span class="h3">Roles</span>
        <b-button variant="success">
          <fa-icon icon="plus" fixed-width /> Add New Role
        </b-button>
       </div>
    </template>
    <b-skeleton-table v-if="state === 'loading'" :table-props="tableProps" :rows="5" :columns="7" />
    <div v-else-if="state === 'error'">
      You are not authorized to view this resource.
    </div>
    <div v-else>
      <b-table :fields="fields" :items="roles" v-bind="tableProps" :busy="state === 'busy'" @row-clicked="rowClicked" />
      <b-pagination class="mx-3" v-model="currentPage" :per-page="perPage" :total-rows="total" :disabled="state !== 'loaded'" />
    </div>

    <b-modal v-model="showView" size="lg" title="Role" >
      <ViewRole :role-id="selected" />
    </b-modal>

    <b-modal v-model="showNew" size="lg">
      <NewRole :role-id="selected" />
    </b-modal>

  </b-card>
</template>

<script>
import { clamp } from 'lodash'
import { DateTime } from 'luxon'
import NewRole from './role/New'
import ViewRole from './role/View'

const fields = [
  {
    key: 'id',
  },
  {
    key: 'name',
    stickyColumn: true,
  },
  {
    key: 'display_name',
  },
  {
    key: 'description',
  },
  {
    key: 'guard_name'
  },
  {
    key: 'updated_at',
    label: 'Last Updated At',
    formatter: (value) => {
      const dt = DateTime.fromISO(value)
      return `${dt.toLocaleString(DateTime.DATETIME_MED)} (${dt.toRelative()})`
    }
  },
]

export default {
  components: {
    NewRole,
    ViewRole,
  },
  data: () => ({
    currentPage: 1,
    state: 'loading', // loading | busy | loaded | error
    perPage: 15,
    roles: [],
    total: 0,
    totalPages: 1,
    selected: null,
    showView: false,
    showNew: false,
    tableProps: {
      'head-variant': 'light',
      responsive: true,
      hover: true,
      striped: true,
      outlined: true,
      'primary-key': 'id',
    },
    fields: fields,
  }),
  methods: {
    load() {
      if (this.state !== 'loading') {
        this.state = 'busy';
      }
      this.axios.get(`/admin/role?page=${this.currentPage}&perPage=${this.perPage}`)
        .then(result => {
          if (result.statusText === 'OK') {
            this.roles = result.data.data
            this.total = result.data.total
            this.totalPages = result.data.last_page
            this.state = 'loaded'
          } else {
            this.state = 'error'
          }
        })
        .catch(error => {
          this.$log.error(error)
        })
    },
    rowClicked(item, index, event) {
      this.selected = item.id
      this.showView = true
    }
  },
  watch: {
    currentPage(value, oldVal) {
      this.currentPage = clamp(value, 1, this.totalPages);
      this.load()
    },
  },
  mounted() {
    this.load()
  },
}
</script>

<style lang="scss" scoped>

</style>
