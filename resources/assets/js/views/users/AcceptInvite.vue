<template>
  <div class="container-xl" id="view-staff_invitations_accpet">
    <div v-if="!isLoading">
      <b-card class="w-50 m-auto">
        <b-card-text v-if="email == session_user.email">
          <h3>Invitation from {{ inviter }}</h3>
          <p class="mt-4 mb-3"><strong>{{ inviter }}</strong> has invited you to work on their team.</p>
          <b-btn variant="primary" @click="acceptInvite">Accept invitation</b-btn>
        </b-card-text>
        <b-card-text v-else>
          <h3>Invitation from {{ inviter }}</h3>
          <p class="mt-4 mb-3"><strong>{{ inviter }}</strong> has invited {{ email }} to work on their team. Please try to login with {{ email }}.</p>
          <b-btn variant="primary" @click="switchAccount">Switch account</b-btn>
        </b-card-text>
      </b-card>
    </div>
  </div>
</template>

<script>
import Vuex from 'vuex'

export default {
  data: () => ({
    inviter: '',
    email: '',
  }),

  computed: {
    ...Vuex.mapState(['session_user']),
  
    isLoading() {
      return !this.session_user
    },
  },
  
  watch: {
    session_user(value) {
      if (value) {

      }
    }
  },

  mounted() {
    const { is_new, logged_in, inviter, email } = this.$route.query;

    if (logged_in || this.session_user) {
      // Logged in user
      this.getMe();
      this.inviter = inviter;
      this.email = email;
    } else {
      // Not logged in user
      if (is_new === 'true') {
        this.$router.push({ name: 'register', params: { redirect: this.$route.fullPath, email } })
      } else {
        this.$router.push({ name: 'login', params: { redirect: this.$route.fullPath, email } })
      }
    }
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
    ]),

    acceptInvite() {

    },
    
    switchAccount() {

    }
  }

}
</script>

<style lang="scss" scoped>
  .capitalize {
    text-transform: capitalize;
  }
</style>
