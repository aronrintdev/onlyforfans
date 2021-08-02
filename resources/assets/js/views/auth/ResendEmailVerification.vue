<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="login-card mx-auto">
      <template #header>
        <div class="h2 text-center mb-0" v-text="$t('header')" />
        <div class="d-flex mt-2">
          <router-link :to="{ name: 'login' }" class="ml-auto" v-text="$t('returnToLogin')" />
        </div>
      </template>
      <transition-group tag="div" name="quick-fade" mode="out-in">
        <div v-if="state === 'form'" key="form">
          <b-form-group :label="$t('email')">
            <b-form-input v-model="email" />
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
    </b-card>

    <div class="mt-auto mb-3">
      <LinkBar />
    </div>
  </div>
</template>

<script>
/**
 * Register Page
 */
import LinkBar from '../../components/staticPages/LinkBar'

export default {
  name: 'ResendEmailVerification',
  components: {
    LinkBar,
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
.login-card {
  width: 500px;
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
