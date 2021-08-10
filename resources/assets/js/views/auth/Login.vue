<template>
  <AuthTemplate>
    <div class="d-flex flex-column px-0 h-100 bg-white">
      <div class="login-form-container p-5">
        <b-form @submit.prevent="login">
          <!-- Login Form -->
          <div class="login-form">
            <Branding type="text" size="3x" class="mb-3 signin-logo" />
            <div class="h1 mb-3" v-text="$t('signInHeader')"></div>
            <div v-if="verrors && verrors.message">
              <b-alert variant="danger" v-text="verrors.message" show />
            </div>
            <b-form-group :invalid-feedback="verrors.email ? verrors.email[0] : null" :state="verrors.email ? false : null">
              <b-form-input
                id="input-email"
                v-model="form.email"
                :placeholder="$t('email')"
                :state="verrors.email ? false : null"
                :disabled="isEmailDisabled"
                @focus="clearVerrors"
              />
            </b-form-group>
            <b-form-group :invalid-feedback="verrors.password ? verrors.password[0] : null" :state="verrors.password ? false : null">
              <b-form-input
                id="input-password"
                type="password"
                v-model="form.password"
                :placeholder="$t('password')"
                :state="verrors.password ? false : null"
                @focus="clearVerrors"
              />
            </b-form-group>
            <div class="text-right">
              <!-- TODO: Link to forgot password page -->
              <router-link :to="{ name: 'forgot-password' }" v-text="$t('forgotPasswordLink')" />
            </div>
          </div>

          <div class="mt-3 mb-5">
            <b-btn type="submit" variant="primary" class="cta-btn" block :disabled="state === 'loading'">
              <span v-if="state === 'form'">{{ $t('signInButton') }}</span>
              <fa-icon v-else icon="spinner" spin />
            </b-btn>
          </div>
        </b-form>

        <div class="mb-3">
          <b-btn class="cta-btn social-btn facebook" block @click="socialLogin('facebook')">
            <fa-icon :icon="['fab', 'facebook-f']" class="mr-2" />
            <span>{{ $t('continueWithFacebook') }}</span>
          </b-btn>
          <b-btn class="cta-btn social-btn google" block @click="socialLogin('google')">
            <fa-icon :icon="['fab', 'google']" class="mr-2" />
            <span>{{ $t('continueWithGoogle') }}</span>
          </b-btn>
          <b-btn class="cta-btn social-btn twitter" block @click="socialLogin('twitter')">
            <fa-icon :icon="['fab', 'twitter']" class="mr-2" />
            <span>{{ $t('continueWithTwitter') }}</span>
          </b-btn>
        </div>

        <div class="divider d-flex">
          <hr class="h-line flex-grow-1" />
          <!-- <div class="mx-3" v-text="$t('or')" /> -->
          <hr class="h-line flex-grow-1" />
        </div>

        <div class="d-flex text-center auth-bottom">
          <p class="text-secondary mt-0 mr-3">Don't have an account?</p>
          <router-link :to="{ name: 'register' }" v-text="$t('signUpLink')" />
        </div>
      </div>
    </div>
  </AuthTemplate>
</template>

<script>
/**
 * Login Page
 */
import LinkBar from '../../components/staticPages/LinkBar'
import AuthTemplate from './AuthTemplate'

import '../../../static/images/g-login-btn.png'
import '../../../static/images/facebook-login.png'
import '../../../static/images/twitter-login.png'

import Branding from '@components/common/Branding'

export default {
  name: 'login',
  components: {
    Branding,
    LinkBar,
    AuthTemplate,
  },
  data: () => ({
    state: 'form', // form | loading
    verrors: {}, // rendered validation Errors
    form: {
      email: '',
      password: '',
      remember: false,
    },
    isEmailDisabled: false,
  }),
  methods: {
    clearVerrors() {
      this.verrors = {}
    },

    async login() {
      await this.$recaptchaLoaded();

      // Execute reCAPTCHA with action "login".
      const token = await this.$recaptcha('login');

      console.log('recaptcha token: ', token);

      this.state = 'loading';
      this.axios.post('/login', { ...this.form, 'g-recaptcha-response': token }).then((response) => {
        if (response.data.redirect) {
          console.log('success', { 
            redirect: response.data 
          });
          if (this.$route.params.redirect) {
            window.location.href = `${this.$route.params.redirect}&logged_in=true`;
          } else {
            window.location = response.data.redirect;
          }
        } else {
          // %TODO
        }
        this.state = 'form';
      }).catch( e => {
        const response = e.response;
        if (response.data && response.data.errors) {
          this.verrors = response.data.errors; // L8 convention is 'errors'
        }
        this.state = 'form';
      });
    },
    socialLogin: function(path) {
      window.location.href = `/${path}`;
    }
  },
  mounted() {
    const { email: emailFromParams } = this.$route.params;
    const { email: emailFromQuery } = this.$route.query;
    const email = emailFromParams || emailFromQuery;
    if (email) {
      this.form = {
        ...this.form,
        email,
      }
      this.isEmailDisabled = true;
    }
  }
}
</script>

<style lang="scss" scoped>
.login-form-container {
  max-width: 600px;
}
.h-line {
  color: var('--gray');
}
.auth-bottom {
  @media (max-width: 576px) {
    flex-direction: column;
  }
}
.cta-btn {
  height: 42px;
  border: none;
  font-size: 14px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;

  &.facebook { background-color: #3B5998; }
  &.google { background-color: #dd4b39; }
  &.twitter { background-color: #55ACEE; }
}
.signin-logo {
  display: none;
  @media (max-width: 576px) {
    display: block;
    width: 154px;
    margin: 0 auto 25px;
  }
}
</style>

<i18n lang="json5">
{
  "en": {
    "signInHeader": "Sign In",
    "noAccountQuestion": "Don't have an account?",
    "signUpLink": "Sign Up",
    "email": "Email or username",
    "password": "Password",
    "signInButton": "Sign In",
    "forgotPasswordLink": "Forgot Password?",
    "or": "or",
    "continueWithFacebook": "Continue With Facebook",
    "continueWithGoogle": "Continue With Google",
    "continueWithTwitter": "Continue With Twitter"
  },
}
</i18n>
