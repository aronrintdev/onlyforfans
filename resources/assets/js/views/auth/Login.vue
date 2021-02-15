<template>
  <div>
    <b-card class="login-card mx-auto" no-body>
      <template #header>
        <div class="h1 text-center" v-text="$t('signInHeader')" />
        <div class="text-center">
          <div class="d-inline" v-text="$t('noAccountQuestion')" />
          <!-- TODO: Link to registration page -->
          <a href="#" v-text="$t('signUpLink')" />
        </div>
      </template>
      <!-- Login Form -->
      <div class="login-form p-3">
        <div v-if="error">
          <b-alert variant="danger" v-text="error.message" />
        </div>
        <b-form-group>
          <b-form-input id="input-email" v-model="form.email" :placeholder="$t('email')" />
        </b-form-group>
        <b-form-group>
          <b-form-input id="input-password" type="password" v-model="form.password" :placeholder="$t('password')" />
        </b-form-group>
        <div class="text-right">
          <!-- TODO: Link to forgot password page -->
          <a href="#" v-text="$t('forgotPasswordLink')" />
        </div>
      </div>

      <div class="p-3">
        <b-btn variant="primary" block @click="login">
          {{ $t('signInButton') }}
        </b-btn>
      </div>

      <div class="divider d-flex">
        <hr class="h-line flex-grow-1" />
        <div class="mx-3" v-text="$t('or')" />
        <hr class="h-line flex-grow-1" />
      </div>
      <div class="3rd-party-sign-in p-3 text-center">
        <!-- TODO: Add 3rd party sign ins -->
        Sign in with 3rd party here.
      </div>
    </b-card>
  </div>
</template>

<script>
export default {
  data: () => ({
    state: 'login',
    error: {},
    form: {
      email: '',
      password: '',
    },

  }),
  methods: {
    login() {
      this.axios.post()
    }
  },
}
</script>

<style lang="scss" scoped>
.login-card {
  max-width: 500px;
}
.h-line {
  color: var('--gray');
}
</style>

<i18n lang="json5">
{
  "en": {
    "signInHeader": "Sign In",
    "noAccountQuestion": "Don't have an account?",
    "signUpLink": "Sign Up",
    "email": "Email",
    "password": "Password",
    "signInButton": "Sign In",
    "forgotPasswordLink": "Forgot Password?",
    "or": "or",
  }
}
</i18n>

