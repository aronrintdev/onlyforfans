<template>
  <div v-if="!isLoading">

    <b-card title="Update Password">
      <b-card-text>
        <b-form @submit.prevent="submitPassword($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formPassword">

            <b-row>
              <b-col>
                <b-form-group id="group-oldPassword" label="Current Password" label-for="oldPassword">
                  <b-form-input type="password" id="oldPassword" v-model="formPassword.oldPassword" placeholder="Enter old password..." ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-newPassword" label="New Password" label-for="newPassword">
                  <b-form-input type="password" id="newPassword" v-model="formPassword.newPassword" placeholder="Enter new password..." aria-describedby="password-help-block"></b-form-input>
                      <b-form-text id="password-help-block">Your password must be 8-20 characters long, contain letters and numbers. We also recommend special characters for enhanced security.</b-form-text>
                </b-form-group>
              </b-col>
            </b-row>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formPassword" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formPassword=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formPassword=true" class="w-25" variant="warning">Edit</b-button>
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

    isEditing: {
      formPassword: false,
    },

    formPassword: {
      oldPassword: '',
      newPassword: '',
    },

  }),

  watch: {
    session_user(newVal) {
    },
  },

  methods: {
    async submitPassword(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/updatePassword`, this.formPassword);
      this.isEditing.formPassword = false;
    },

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>

