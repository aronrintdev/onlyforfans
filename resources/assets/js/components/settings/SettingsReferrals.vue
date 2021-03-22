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
    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  watch: { },

  data: () => ({
  }),

  created() {
  },

  methods: {
  },

}
</script>

<style scoped>
</style>
