<template>
  <AuthTemplate>
    <div class="h-100 d-flex flex-column px-0 bg-white">
      <div class="forgot-password-form-container p-5">
        <Branding type="text" size="3x" class="signin-logo" />
        <div class="h2 d-md-none text-center my-3" v-text="`Forgot Password`" />
        <div class="h1 d-none d-md-block text-md-left my-3" v-text="`Forgot Password`" />
        <div class="my-2 text-center text-md-left">
          <router-link :to="{ name: 'login' }">Return to Sign In</router-link>
        </div>

        <!-- <b-alert variant="info" show>
          If you did not receive an email verification request after signing up, please use the
          <router-link :to="{ name: 'resend-email' }">resend email verification</router-link> page.
        </b-alert> -->

        <!-- Forgot Password Form -->
        <b-form @submit.prevent="sendForgotPassRequest">
          <div class="login-form">
            <div class="text-left mt-2 mb-3">
              <div class="d-inline" v-text="`If you have an AllFans account, enter your email address below and you will receive a link to reset your password.`" />
            </div>
            <b-form-group
              :invalid-feedback="verrors.email ? verrors.email[0] : null"
              :state="verrors.email ? false : null"
              v-if="state !== 'submitted'"
            >
              <b-form-input
                id="input-email"
                v-model="form.email"
                placeholder="Email"
                :state="verrors.email ? false : null"
                @focus="clearVerrors"
              />
            </b-form-group>
            <b-alert show variant="info" class="text-center" v-else>
              If we found a match in our database, an email has been dispatched with instructions to reset your password.
            </b-alert>
          </div>

          <div class="pb-3" v-if="state !== 'submitted'">
            <b-btn type="submit" class="p-2 cta-btn" :class="state === 'loading' ? 'loading' : ''" variant="primary" block :disabled="!form.email">
              <span v-if="state === 'form'">Send</span>
              <fa-icon v-else icon="spinner" spin />
            </b-btn>
          </div>
          <div class="text-center mb-3" v-else>
              <!-- TODO: Link to forgot password page -->
              <router-link :to="{ name: 'login' }" v-text="$t('signInLink')" />
            </div>
        </b-form>
      </div>
    </div>
  </AuthTemplate>
</template>

<script>
/**
 * Forgot Password Page
 */
import AuthTemplate from './AuthTemplate'

import Branding from '@components/common/Branding'

export default {
  name: 'forgot-pass',
  components: {
    Branding,
    AuthTemplate,
  },
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
        this.state = 'submitted'
        // this.$router.push('/');
      }).catch( e => {
        const response = e.response
        console.log('errors: ', response);
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
.forgot-password-form-container {
  max-width: 600px;
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


<i18n lang="json5">
{
  "en": {
    "signInLink": "Sign In",
  },
}
</i18n>
