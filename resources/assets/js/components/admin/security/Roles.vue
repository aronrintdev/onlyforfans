<template>
  <b-card no-body>
    <template #header>
      <div class="d-flex justify-content-between">
        <span class="h3">Roles</span>
        <b-button variant="success" @click="showNew = true">
          <fa-icon icon="plus" fixed-width /> Add New Role
        </b-button>
      </div>
    </template>
    <b-skeleton-table v-if="state === 'loading'" :table-props="tableProps" :rows="5" :columns="7" />
    <error-alert v-else-if="state === 'error'" title="Unauthorized"
      >You are not authorized to view this resource.</error-alert
    >
    <div v-else>
      <b-table
        :fields="fields"
        :items="roles"
        v-bind="tableProps"
        :busy="state === 'busy'"
        @row-clicked="rowClicked"
      >
        <template #cell(actions)="data">
          <div>
            <b-btn
              variant="info"
              size="sm"
              v-b-tooltip.hover
              title="What users have this role?"
              @click="viewUsers(data.item)"
            >
              <fa-icon icon="users" />
            </b-btn>
          </div>
        </template>
      </b-table>
      <b-pagination
        class="mx-3"
        v-model="currentPage"
        :per-page="perPage"
        :total-rows="total"
        :disabled="state !== 'loaded'"
      />
    </div>

    <b-modal v-model="showView" size="xl" :title="`Role ${selected.display_name || selected.name}`" @shown="$forceUpdate()">
      <ViewRole ref="viewRole" :role-id="selected.id" v-model="showView" />
    </b-modal>

    <b-modal v-model="showNew" size="lg">
      <NewRole :role-id="selected.id" />
    </b-modal>

    <b-modal
      v-model="showUsers"
      size="xl"
      :title="`Users with the Role ${selected.display_name || selected.name}`"
      @shown="$refs.userList.load()"
    >
      <UserList ref="userList" :endpoint="getUserEndpoint()" />
    </b-modal>
  </b-card>
</template>

<script>
import { clamp } from 'lodash'
import { DateTime } from 'luxon'
import { NewRole, ViewRole } from './role'
import UserList from '../users/List.vue'

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
    key: 'guard_name',
  },
  {
    key: 'updated_at',
    label: 'Last Updated At',
    formatter: (value) => {
      const dt = DateTime.fromISO(value)
      return `${dt.toLocaleString(DateTime.DATETIME_MED)} (${dt.toRelative()})`
    },
  },
  {
    key: 'actions',
    label: 'Actions',
  },
]

export default {
  components: {
    NewRole,
    ViewRole,
    UserList,
  },
  data: () => ({
    currentPage: 1,
    state: 'loading', // loading | busy | loaded | error
    perPage: 15,
    roles: [],
    total: 0,
    totalPages: 1,
    selected: {},
    showView: false,
    showNew: false,
    showUsers: false,
    tableProps: {
      'head-variant': 'light',
      responsive: true,
      hover: true,
      striped: true,
      outlined: true,
      'tbody-tr-class': 'cursor-pointer',
      'primary-key': 'id',
    },
    fields: fields,
  }),
  methods: {
    /**
     * Load a page of roles
     */
    load() {
      if (this.state !== 'loading') {
        this.state = 'busy'
      }
      this.axios
        .get(`/admin/role?page=${this.currentPage}&perPage=${this.perPage}`)
        .then((result) => {
          if (result.statusText === 'OK') {
            this.roles = result.data.data
            this.total = result.data.total
            this.totalPages = result.data.last_page
            this.state = 'loaded'
          } else {
            this.state = 'error'
          }
        })
        .catch((error) => {
          this.$log.error(error)
        })
    },

    /**
     * Table row was clicked
     */
    rowClicked(item, index, event) {
      this.selected = item
      this.showView = true
    },

    /** Get list of users that have the role */
    viewUsers(role) {
      this.selected = role
      this.showUsers = true
    },

    getUserEndpoint() {
      return () => this.$route('admin.role.users', this.selected)
    },
  },
  watch: {
    currentPage(value, oldVal) {
      this.currentPage = clamp(value, 1, this.totalPages)
      this.load()
    },
  },
  mounted() {
    this.load()
  },
}
</script>
