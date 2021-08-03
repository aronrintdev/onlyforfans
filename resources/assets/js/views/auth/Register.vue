<template>
  <AuthTemplate>
    <div class="d-flex flex-column h-100 bg-white">
      <div class="login-card p-5">
        <!-- SignUp Form -->
        <b-form @submit.prevent="signup">
          <img src="/images/logos/allfans-logo-154x33.png" alt="All Fans Logo" class="register-logo">
          <div class="h1 mb-5" v-text="$t('signUpLink')" />
          <div class="signup-form">
            <div v-if="$route.query.beta">
              <b-alert variant="primary" v-text="$t('betaMessage')" show />
            </div>

            <div v-if="verrors && verrors.message">
              <b-alert variant="danger" v-text="verrors.message" show />
            </div>
            <div v-if="verrors && verrors.token">
              <b-alert variant="danger" v-text="$t('betaFailedMessage')" show />
            </div>
            <b-form-group :invalid-feedback="verrors.email ? verrors.email[0] : null" :state="verrors.email ? false : null">
              <b-form-input
                id="input-email"
                v-model="form.email"
                :placeholder="$t('email')"
                :disabled="isEmailDisabled"
                :state="verrors.email ? false : null"
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
            <b-form-group
              :invalid-feedback="verrors.name ? verrors.name[0] : null"
              :state="verrors.name ? false : null"
            >
              <b-form-input
                id="input-name"
                type="text"
                v-model="form.name"
                placeholder="Name"
                :state="verrors.name ? false : null"
                @focus="clearVerrors"
              />
            </b-form-group>
            <b-form-group
              v-if="usernameShown"
              :invalid-feedback="verrors.username ? verrors.username[0] : null"
              :state="verrors.username ? false : null"
            >
              <b-input-group prepend="@">
                <b-form-input
                  id="input-username"
                  type="text"
                  v-model="form.username"
                  placeholder="Username (optional)"
                  :state="verrors.username ? false : null"
                  @focus="clearVerrors"
                />
              </b-input-group>
            </b-form-group>
            <b-form-group :invalid-feedback="verrors.tos ? verrors.tos[0] : null" :state="verrors.tos ? false : null">
              <b-form-checkbox
                id="checkbox-tos"
                v-model="form.tos"
                name="checkbox-tos"
                @focus="clearVerrors"
              >
                I agree to the Terms of Service and Privacy Policy and confirm that I am at least 18 years old.
              </b-form-checkbox>
            </b-form-group>
          </div>

          <div>
            <b-btn type="submit" class="cta-btn" variant="primary" block :disabled="state === 'loading' || !form.tos">
              <span v-if="state === 'form'">{{ $t('signUpLink') }}</span>
              <fa-icon v-else icon="spinner" spin />
            </b-btn>
          </div>
        </b-form>

        <div class="divider d-flex">
          <hr class="h-line flex-grow-1" />
          <div class="mx-3" v-text="$t('or')" />
          <hr class="h-line flex-grow-1" />
        </div>
        
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
          <p class="text-secondary mt-0 mr-3">Do you already have an account?</p>
          <router-link :to="{ name: 'login' }" v-text="$t('signInHeader')" />
        </div>
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

import '../../../static/images/g-login-btn.png'
import '../../../static/images/facebook-login.png'
import '../../../static/images/twitter-login.png'

export default {
  name: 'register',
  components: {
    LinkBar,
    AuthTemplate,
  },
  data: () => ({
    state: 'form', // form | loading
    verrors: {}, // rendered validation Errors
    form: {
      email: '',
      password: '',
      name: '',
      username: '',
      tos: null,
    },
    isEmailDisabled: false,
    usernameShown: false,
  }),

  methods: {
    clearVerrors() {
      this.verrors = {}
    },
    async signup() {
      await this.$recaptchaLoaded();

      // Execute reCAPTCHA with action "register".
      const token = await this.$recaptcha('register');

      this.state = 'loading'

      this.axios.post('/register', {
        ...this.form,
        // Include token if url has one
        token: this.$route.query.token || null,
        ref: this.$route.query.ref,
        'g-recaptcha-response': token
      }).then((response) => {
        if (response.data.err_result) {
          this.verrors = response.data.err_result;
        } else {
          if (this.$route.params.redirect) {
            window.location.href = `${this.$route.params.redirect}&logged_in=true`;
          } else {
            this.$router.push({ name: 'confirm-email' })
          }
        }
        this.state = 'form'
      }).catch( e => {
        const response = e.response
        if (response.data && response.data.errors) {
          this.verrors = response.data.errors // L8 convention is 'errors'
        }
        this.state = 'form'
      });
    },
    socialLogin: function(path) {
      window.location.href = `/${path}`;
    }
  },

  mounted() {
    const { email } = this.$route.params;
    if (email) {
      this.form = {
        ...this.form,
        email,
      }
      this.isEmailDisabled = true;
    }
    if (this.$route.query.email) {
      this.form.email = this.$route.query.email
    }
  }
}
</script>

<style lang="scss" scoped>
.login-card {
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

  &:disabled {
    background: rgba(138,150,163,.75);
    border-color: rgba(138,150,163,.75);
    opacity: .4;
    pointer-events: none;
  }

  &.facebook { background-color: #3B5998; }
  &.google { background-color: #dd4b39; }
  &.twitter { background-color: #55ACEE; }
}
.register-logo {
  display: none;
  @media (max-width: 576px) {
    display: block;
    margin: 0 auto 25px;
  }
}
</style>

<i18n lang="json5">
{
  "en": {
    "signInHeader": "Sign In",
    "accountQuestion": "Already have an account?",
    "signUpLink": "Sign Up",
    "email": "Email",
    "password": "Password",
    "signInButton": "Sign In",
    "forgotPasswordLink": "Forgot Password?",
    "or": "or",
    "continueWithFacebook": "Continue With Facebook",
    "continueWithGoogle": "Continue With Google",
    "continueWithTwitter": "Continue With Twitter",
    "betaMessage": "Welcome to the Allfans.com closed beta. Please complete the form below to register your account",
    "betaFailedMessage": "Allfans.com is currently in a closed beta, please use the link provided to you in your invitation email to register in the beta program"
  },
}
</i18n>
