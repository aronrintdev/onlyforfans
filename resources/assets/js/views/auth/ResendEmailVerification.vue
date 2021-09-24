<template>
  <AuthTemplate>
    <div class="h-100 d-flex flex-column px-0 bg-white">
      <div class="form-container p-5 d-flex flex-column mt-5">
        <Branding type="text" size="3x" class="signin-logo" />
        <div class="h2 d-md-none text-center my-3" v-text="$t('header')" />
        <div class="h1 d-none d-md-block text-md-left my-3" v-text="$t('header')" />
        <div class="my-2 text-center text-md-left">
          <router-link :to="{ name: 'login' }">Return to Sign In</router-link>
        </div>
        <transition-group tag="div" name="quick-fade" mode="out-in">
          <div v-if="state === 'form'" key="form">
            <b-form-group>
              <b-form-input v-model="email" :placeholder="$t('email')" />
            </b-form-group>
            <b-btn variant="primary" block :disabled="email === ''" v-text="$t('send')" @click="send" />
          </div>
          <div v-if="state === 'alreadyConfirmed'" key="alreadyConfirmed">
            <b-alert variant="success" show>
              {{ $t('alreadyConfirmed') }}
            </b-alert>
            <b-btn variant="primary" block :to="{ name: 'login' }">
              {{ $t('returnToLogin') }}
            </b-btn>
          </div>
          <div v-if="state === 'sent'" key="sent">
            <b-alert variant="success" show>
              {{ $t('sent') }}
            </b-alert>
            <b-btn variant="primary" block :to="{ name: 'login' }">
              {{ $t('returnToLogin') }}
            </b-btn>
          </div>
        </transition-group>
      </div>
    </div>
  </AuthTemplate>
</template>

<script>
/**
 * Register Page
 */
import LinkBar from '../../components/staticPages/LinkBar'
import AuthTemplate from './AuthTemplate'
import Branding from '@components/common/Branding'

export default {
  name: 'ResendEmailVerification',
  components: {
    AuthTemplate,
    Branding,
  },

  data: () => ({
    state: 'form', // form | alreadyConfirmed | sent
    email: '',
  }),

  methods: {
    send() {
      if (this.email === '') {
        return
      }
      this.processing = true
      this.axios.post(this.$apiRoute('verification.resend'), { email: this.email })
      .then(response => {
        if (response.data.already_verified) {
          this.state = 'alreadyConfirmed'
        } else {
          this.state = 'sent'
        }
      })
    },

  },

  mounted() {},
}
</script>

<style lang="scss" scoped>
.form-container {
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
    "header": "Resend Email Verification",
    "returnToLogin": "Return to Sign In",
    "message": "Enter your email and press send to resend your verification email",
    "email": "Email",
    "send": "Send",
    "alreadyConfirmed": "Your email address has already been confirmed. Please return to the login screen to log in or reset your password.",
    "sent": "We have sent a verification email to the email you have provided. If you did not receive your verification email please check your spam folder or the email address you used to sign up with."
  },
}
</i18n>
