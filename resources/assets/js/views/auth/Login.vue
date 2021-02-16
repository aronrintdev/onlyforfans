<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="login-card mx-auto" no-body>
      <template #header>
        <div class="h1 text-center" v-text="$t('signInHeader')" />
        <div class="text-center">
          <div class="d-inline" v-text="$t('noAccountQuestion')" />
          <!-- TODO: Link to registration page -->
          <router-link :to="{ name: 'register' }" v-text="$t('signUpLink')" />
        </div>
      </template>
      <!-- Login Form -->
      <div class="login-form p-3">
        <div v-if="error && error.message">
          <b-alert variant="danger" v-text="error.message" show />
        </div>
        <b-form-group :invalid-feedback="error.email ? error.email[0] : null" :state="error.email ? false : null">
          <b-form-input
            id="input-email"
            v-model="form.email"
            :placeholder="$t('email')"
            :state="error.email ? false : null"
          />
        </b-form-group>
        <b-form-group :invalid-feedback="error.password ? error.password[0] : null" :state="error.password ? false : null">
          <b-form-input
            id="input-password"
            type="password"
            v-model="form.password"
            :placeholder="$t('password')"
            :state="error.password ? false : null"
          />
        </b-form-group>
        <div class="text-right">
          <!-- TODO: Link to forgot password page -->
          <router-link :to="{ name: 'forgot-password' }" v-text="$t('forgotPasswordLink')" />
        </div>
      </div>

      <div class="p-3">
        <b-btn variant="primary" block @click="login" :disabled="state === 'loading'">
          <span v-if="state === 'form'">{{ $t('signInButton') }}</span>
          <fa-icon v-else icon="spinner" spin />
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

    <div class="mt-auto mb-3">
      <LinkBar />
    </div>
  </div>
</template>

<script>
/**
 * Login Page
 */
import LinkBar from '../../components/staticPages/LinkBar'
export default {
  name: 'login',
  components: {
    LinkBar,
  },
  data: () => ({
    state: 'form', // form | loading
    error: {},
    form: {
      email: '',
      password: '',
      remember: false,
    },
  }),
  methods: {
    login() {
      this.state = 'loading'
      this.axios.post('/login', this.form).then((response) => {
        if (response.data.error) {
          this.error = response.data.error
        } else if (response.data.redirect) {
          window.location = response.data.redirect
        }
        this.state = 'form'
      })
    },
  },
}
</script>

<style lang="scss" scoped>
.login-card {
  width: 500px;
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
  },
}
</i18n>
