<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="form-card mx-auto" no-body>
      <template #header>
        <div class="h2 text-center mb-0" v-text="`Reset Password`" />
      </template>
      <div class="login-form p-3">
        <div v-if="verrors && verrors.message">
          <b-alert variant="danger" v-text="verrors.message" show />
        </div>
        <b-form-group class="mt-2 mb-4" :invalid-feedback="verrors.password ? verrors.password[0] : null" :state="verrors.password ? false : null">
          <b-form-input
            id="input-password"
            type="password"
            v-model="form.password"
            placeholder="Password"
            :state="verrors.password ? false : null"
            @focus="clearVerrors"
          />
        </b-form-group>
        <b-form-group class="mb-2 mt-3" :invalid-feedback="verrors.confirm_password ? verrors.confirm_password[0] : null" :state="verrors.confirm_password ? false : null">
          <b-form-input
            id="input-confirm_password"
            type="password"
            v-model="form.confirm_password"
            placeholder="Confirm Password"
            :state="verrors.confirm_password ? false : null"
            @focus="clearVerrors"
          />
        </b-form-group>
      </div>

      <div class="pl-3 pr-3 pb-3">
        <b-btn class="p-2 cta-btn" :class="state === 'loading' ? 'loading' : ''" variant="primary" block @click="sendResetPassRequest" :disabled="!form.password || !form.confirm_password">
          <span v-if="state === 'form'">Reset</span>
          <fa-icon v-else icon="spinner" spin />
        </b-btn>
      </div>
    </b-card>
  </div>
</template>

<script>
/**
 * Reset Password Page
 */
export default {
  name: 'reset-pass',
  data: () => ({
    state: 'form', // form | loading
    verrors: {}, // rendered validation Errors
    form: {
      email: '',
      password: '',
      confirm_password: '',
    },
  }),
  methods: {
    clearVerrors() {
      this.verrors = {}
    },
    checkValidation() {
      if (this.form.password && this.form.confirm_password) {
        if (this.form.password !== this.form.confirm_password) {
          this.verrors = { confirm_password: ['Password and Confirm password do not match.'] };
          return false;
        }
        return true;
      }
      return false;
    },
    sendResetPassRequest() {
      if (!this.checkValidation()) {
        return;
      }
      this.state = 'loading';
      const { email } = this.$route.query;
      const form = {
        password: this.form.password,
        email,
      }
      this.axios.post('/password/reset', form).then((response) => {
        const { data } = response;
        this.state = 'form'

        if (data.status > 200) {
          this.verrors = { message: data.message };
          if (data.err_result) {
            this.verrors = response.data.err_result;
          }
        } else {
          this.$router.push('/login');
        }
      }).catch( e => {
        const response = e.response
        if (response.data && response.data.errors) {
          this.verrors = response.data.errors
        }
        this.state = 'form'
      });
    },
  },
  mounted: function() {
    const { token } = this.$route.params;
    const { email } = this.$route.query;
    if (token && email) {
      this.axios.post(`/password/reset/${token}?email=${email}`).then((response) => {
        const { data } = response;
        if (data.status > 200) {
          this.verrors = { message: data.message };
        }
      }).catch( e => {
        this.verrors = { message: e.message };
      });
    } else {
      this.verrors = { message: 'Token or Email are missing' };
    }
  }
}
</script>

<style lang="scss" scoped>
.form-card {
  width: 500px;
}
.cta-btn {
  height: 42px;
  border: none;
  font-size: 14px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;

  &.loading {
    opacity: 0.65;
    pointer-events: none;
  }

  &:disabled {
    background: rgba(138,150,163,.75);
    border-color: rgba(138,150,163,.75);
    opacity: .4;
    pointer-events: none;
  }
}
</style>