<template>
  <div class="container-xl" id="view-staff_invitations_accpet">
    <div v-if="!isLoading">
      <b-card class="w-50 m-auto">
        <b-card-text v-if="email == session_user.email">
          <h3>Invitation from {{ inviter }}</h3>
          <p class="mt-4 mb-3"><strong>{{ inviter }}</strong> has invited you to work on their team.</p>
          <b-btn variant="primary" @click="acceptInvite" :disabled="isProcessing">
            <b-spinner v-if="isProcessing" small></b-spinner>&nbsp;
            Accept invitation
          </b-btn>
        </b-card-text>
        <b-card-text v-else>
          <h3>Invitation from {{ inviter }}</h3>
          <p class="mt-4 mb-3"><strong>{{ inviter }}</strong> has invited <em>{{ email }}</em> to work on their team. Please try to login with that email.</p>
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
    isProcessing: false,
  }),

  computed: {
    ...Vuex.mapGetters([
      'session_user', 
    ]),

    isLoading() {
      return !this.session_user
    },
  },

  watch: {
    session_user(value) {
      if (value) {
        const { logged_in, inviter, email } = this.$route.query;

        if (logged_in || value) {
          this.inviter = inviter;
          this.email = email;
        }
      }
    }
  },

  mounted() {
    this.getMe()
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
    ]),

    acceptInvite() {
      this.isProcessing = true;
      const { token, email } = this.$route.query;
      const formData = {
        email: email,
        token: token, 
      };
      this.axios.post(this.$apiRoute('staff.acceptInvite'), formData)
        .then(response => {
          this.isProcessing = false;
          this.$router.push('/');
        })
        .catch(error => {
          this.isProcessing = false;
          this.$root.$bvToast.toast('Invalid token or email', {
            toaster: 'b-toaster-top-center',
            title: 'Failed!',
            variant: 'danger',
          })
        })
    },
    
    switchAccount() {
      Echo.leave('user-status');
      window.setLastSeenOfUser(0);
      this.axios.post('/logout').then(() => {
        window.location.reload();
      })
    }
  }

}
</script>

<style lang="scss" scoped>
  .capitalize {
    text-transform: capitalize;
  }
</style>
