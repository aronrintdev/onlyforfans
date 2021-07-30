<template>
  <div>
    <error-alert v-if="state === 'error'" />
    <div v-else>
      <div v-if="state === 'loaded'" class="mb-2">
        Total: {{ total }}
      </div>
      <b-list-group>
        <b-list-group-item v-if="state === 'loading'">
          <b-media>
            <template #aside >
              <b-skeleton type="avatar"/>
            </template>
            <div class="h4"><b-skeleton width="25%" height="1.5rem" /></div>
            <p><b-skeleton width="20%" /></p>
          </b-media>
        </b-list-group-item>
        <b-list-group-item v-for="user in usersList" :key="user.id">
          <b-media>
            <template #aside >
              <b-avatar :src="user.avatar.filepath || null" />
            </template>
            <div class="h4">{{ user.name }}</div>
            <p>{{ user.email }}</p>
            <p v-if="user.roles">
              <b-badge v-for="role in user.roles" :key="`${user.id}-${role.id}`" class="mr-2">
                {{ role.display_name || role.name }}
              </b-badge>
            </p>
          </b-media>
        </b-list-group-item>
        <b-list-group-item v-if="state === 'loaded' && usersList.length === 0">
          <div class="w-100 text-center">No Users</div>
        </b-list-group-item>
      </b-list-group>
      <b-pagination
        class="mt-3 mx-3"
        v-model="currentPage"
        :per-page="perPage"
        :total-rows="total"
        :disabled="state !== 'loaded'"
      />
    </div>
  </div>
</template>

<script>
import { clamp } from 'lodash'

export default {
  props: {
    users: { type: Array, default: () => [] },
    endpoint: { type: [String, Function], default: null },
    endpointMethod: { type: String, default: 'get' },
    perPage: { type: Number, default: 15 },
  },
  data: () => ({
    usersList: [],
    currentPage: 1,
    total: 0,
    totalPages: 1,
    state: 'loading', // loading | loaded | error
  }),
  methods: {
    load() {
      if (this.endpoint) {
        this.state = 'loading'
        const url = typeof this.endpoint === 'string' ? this.endpoint : this.endpoint()
        this.axios[this.endpointMethod](`${url}?page=${this.currentPage}&perPage=${this.perPage}`)
          .then((result) => {
            if (result.statusText === 'OK') {
              if ( result.data.current_page ) {
                this.$log.debug('result.data.current_page')
                this.currentPage = result.data.current_page
                this.total = result.data.total
                this.totalPages = result.data.last_page
                this.usersList = result.data.data || []
              } else if (result.data.users) {
                this.usersList = result.data.users
              } else if (result.data.data) {
                this.usersList = result.data.data
              } else {
                this.usersList = result.data
              }
              this.state = 'loaded'
            } else {
              this.state = 'error'
            }
          })
          .catch((error) => {
            this.$log.error(error)
            this.state = 'error'
          })
      }
    },
  },
  watch: {
    currentPage(value) {
      this.currentPage = clamp(value, 1, this.totalPages)
      this.load()
    },
  },
  mounted() {
    if (typeof this.endpoint !== 'undefined') {
      this.load()
    } else {
      this.users = this.usersList
      this.state = 'loaded'
    }
  },
}
</script>
