<template>
  <div v-if="!isLoading">
    <b-card title="Login Sessions" class="mb-3">
      <b-card-text>
        <b-table hover 
          id="loginSessions-table"
          :items="login_sessions.data"
          :fields="fields"
          :current-page="currentPage"
        ></b-table>
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          aria-controls="loginSessions-table"
          v-on:page-click="pageClickHandler"
        ></b-pagination>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue' // needed to use niceCurrency filter in table formatter below
import Vuex from 'vuex'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    ...Vuex.mapState(['login_sessions']),

    totalRows() {
      return this.login_sessions.meta ? this.login_sessions.meta.total : 1;
    },

    isLoading() {
      return this.is_loginSessions_loading;
    },
  },

  watch: {
    login_sessions(value) {
      if (value) {
        this.is_loginSessions_loading = false
      }
    },
  },

  data: () => ({

    is_loginSessions_loading: true,

    perPage: 10,
    currentPage: 1,

    fields: [
      {
        //key: 'user_id',
        key: 'user.username',
        label: 'Username',
      },
      /*
      {
        key: 'browser',
        label: 'Browser',
      },
       */
      {
        key: 'ip_address',
        label: 'IP Address',
      },
      {
        key: 'user_agent',
        label: 'OS',
      },
      /*
      {
        key: 'machine_name',
        label: 'Machine Name',
      },
       */
      {
        key: 'last_activity',
        label: 'Latest Activity',
        formatter: (value, key, item) => {
          return moment.unix(value).format('MMMM Do, YYYY')
        }
      },
    ],

  }),

  created() {
    this.getLoginSessions({ 
      seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
  },

  methods: {
    ...Vuex.mapActions({
      getLoginSessions: "getLoginSessions",
    }),

    pageClickHandler(e, page) {
      this.getFanledgers({ 
        seller_id: this.session_user.id,
        page: page,
        take: this.perPage,
      })
    },

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>
