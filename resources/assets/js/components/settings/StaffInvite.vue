<template>
  <b-card class="mb-3">
    <b-card-text>
      <a class="text-primary text-decoration-none link-btn" @click="goBack">
        <fa-icon :icon="['far', 'arrow-left']" />
        <span class="px-1">{{ forManager ? 'Managers' : 'Staff members' }}</span>
      </a>
      <div class="d-flex align-items-center my-4">
        <div class="h3 text-center" v-if="forManager">Invite Manager</div>
        <div class="h3 text-center" v-else>Invite Staff Member</div>
      </div>
      <b-form @submit.prevent="submitForm">
        <b-row>
          <b-col>
            <b-form-group label="First name" label-for="first_name">
              <b-form-input id="first_name" v-model="formData.first_name" placeholder="First name" ></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Last name" label-for="last_name">
              <b-form-input id="last_name" v-model="formData.last_name" placeholder="Last name" ></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <b-form-group label="Email" label-for="email">
              <b-form-input id="email" v-model="formData.email" type="email" placeholder="Email" ></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row class="mt-3">
          <b-col>
            <div class="w-100 d-flex justify-content-end">
              <b-button :disabled="isSubmitting" class="w-25 ml-3" type="submit" variant="primary">
                <b-spinner v-if="isSubmitting" small />&nbsp;
                Send invite
              </b-button>
            </div>
          </b-col>
        </b-row>

      </b-form>
    </b-card-text>
  </b-card>
</template>

<script>
  export default {
    props: {
      items: { type: Array, default: () => ([])},
      forManager: { type: Boolean, default: true },
      session_user: null,
    },

    data: () => ({
      isSubmitting: false,
      formData: {},
    }),

    methods: {
      goBack() {
        this.$emit('exit');
      },
      submitForm() {
        this.isSubmitting = true;
        // Call Invite REST API
        const formData = {
          ...this.formData,
          pending: 1,
          role: this.forManager ? 'manager' : 'staff',
          name: `${this.formData.first_name} ${this.formData.last_name}`,
        }
        this.axios.post(this.$apiRoute('users.sendStaffInvite'), formData).then(() => {
          this.$emit('send');
          this.isSubmitting = false;
        })
        .catch(error => {
          console.error(error.message);
          this.isSubmitting = false;
        })
      }
    },

  }
</script>

<style lang="scss" scoped>
.link-btn {
  cursor: pointer;
}
</style>