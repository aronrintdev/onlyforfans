<template>
  <div v-if="!isLoading">
    <b-card title="Update Password">
      <b-card-text>
        <b-form @submit.prevent="submitPassword($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formPassword">
            <b-row>
              <b-col>
                <FormTextInput ikey="oldPassword" itype="password" label="Current Password" placeholder="Enter old password..." v-model="formPassword.oldPassword" :verrors="verrors" />
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

  </div>
</template>

<script>
//import Vuex from 'vuex';
import FormTextInput from '@components/forms/elements/FormTextInput'

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

  data: () => ({
    isSubmitting: {
      formPassword: false,
    },

    verrors: null,

    formPassword: {
      oldPassword: '',
      newPassword: '',
    },
  }),

  methods: {
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

<style scoped>
</style>

