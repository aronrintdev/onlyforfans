<template>
  <div v-if="!isLoading">
    <b-card title="Change Password">
      <b-card-text>
        <b-form @submit.prevent="submitPassword($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formPassword">
            <b-row>
              <b-col>
                <FormTextInput ikey="oldPassword" itype="password" label="Current Password" placeholder="Enter current password..." v-model="formPassword.oldPassword" :verrors="verrors" />
              </b-col>
              <b-col>
                <FormTextInput ikey="newPassword" itype="password" label="New Password" placeholder="Enter new password..." v-model="formPassword.newPassword" :verrors="verrors" />
              </b-col>
            </b-row>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting.formPassword" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting.formPassword" small />&nbsp;
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Login Sessions" class="mt-5">
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
//import Vuex from 'vuex';
import FormTextInput from '@components/forms/elements/FormTextInput'
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
      return !this.session_user || !this.user_settings
    },
  },

  data: () => ({
    isSubmitting: {
      formPassword: false,
    },

    verrors: null,

    formPassword: {
      oldPassword: '',
      newPassword: '',
    },

    is_loginSessions_loading: true,

    perPage: 10,
    currentPage: 1,

    fields: [
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
      //
    },

    async submitPassword(e) {
      this.isSubmitting.formPassword = true

      try {
        const response = await axios.patch(`/users/${this.session_user.id}/updatePassword`, this.formPassword)
        this.$root.$bvToast.toast('Password has been updated successfully!', {
          toaster: 'b-toaster-top-center',
          title: 'Success',
          variant: 'success',
        })
      } catch (err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formPassword = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  components: {
    FormTextInput,
  },
}
</script>

<style>
  #loginSessions-table thead th:first-child,
  #loginSessions-table thead th:last-child {
    white-space: nowrap;
  }
</style>

