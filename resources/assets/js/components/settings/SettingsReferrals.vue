<template>
  <div v-if="!isLoading">
    <b-card title="Referrals" class="mb-3">
      <b-card-text>
        TBD
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

    /*
    totalRows() {
      return this.referrals.meta ? this.referrals.meta.total : 1;
    },
     */

    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  watch: { },

  data: () => ({

    perPage: 10,
    currentPage: 1,

    fields: [
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
  },

  methods: {
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
