<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="form-card mx-auto" no-body>
      <template #header>
        <div class="h2 text-center mb-0" v-text="`Forgot Password`" />
      </template>
      <!-- Login Form -->
      <div class="login-form p-3">
        <div class="text-left mt-2 mb-3">
          <div class="d-inline" v-text="`If you have an AllFans account, you will receive a password reset link to this e-mail.`" />
        </div>
        <b-form-group :invalid-feedback="verrors.email ? verrors.email[0] : null" :state="verrors.email ? false : null">
          <b-form-input
            id="input-email"
            v-model="form.email"
            placeholder="Email"
            :state="verrors.email ? false : null"
            @focus="clearVerrors"
          />
        </b-form-group>
      </div>

      <div class="pl-3 pr-3 pb-3">
        <b-btn class="p-2 cta-btn" :class="state === 'loading' ? 'loading' : ''" variant="primary" block @click="sendForgotPassRequest" :disabled="!form.email">
          <span v-if="state === 'form'">Send</span>
          <fa-icon v-else icon="spinner" spin />
        </b-btn>
      </div>
    </b-card>
  </div>
</template>

<script>
/**
 * Forgot Password Page
 */
export default {
  name: 'forgot-pass',
  data: () => ({
    state: 'form', // form | loading
    verrors: {}, // rendered validation Errors
    form: {
      email: '',
    },
  }),
  methods: {
    clearVerrors() {
      this.verrors = {}
    },
    sendForgotPassRequest() {
      this.state = 'loading'
      this.axios.post('/forgot-password', this.form).then((response) => {
        this.state = 'form'
        this.$router.push('/');
      }).catch( e => {
        const response = e.response
        if (response.data && response.data.errors) {
          this.verrors = response.data.errors // L8 convention is 'errors'
        }
        this.state = 'form'
      });
    },
  },
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