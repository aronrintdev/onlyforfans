<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="login-card mx-auto" no-body>
      <template #header>
        <div class="h2 text-center" v-text="$t('signUpLink')" />
        <div class="text-center">
          <div class="d-inline" v-text="$t('accountQuestion')" />
          <!-- TODO: Link to Login page -->
          <router-link :to="{ name: 'login' }" v-text="$t('signInHeader')" />
        </div>
      </template>
      <!-- SignUp Form -->
      <b-form @submit.prevent="signup">
        <div class="signup-form p-3">
          <div v-if="verrors && verrors.message">
            <b-alert variant="danger" v-text="verrors.message" show />
          </div>
          <b-form-group :invalid-feedback="verrors.email ? verrors.email[0] : null" :state="verrors.email ? false : null">
            <b-form-input
              id="input-email"
              v-model="form.email"
              :placeholder="$t('email')"
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

        <div class="p-3">
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
      
      <div class="p-3 mb-3">
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

import '../../../static/images/g-login-btn.png'
import '../../../static/images/facebook-login.png'
import '../../../static/images/twitter-login.png'

export default {
  name: 'register',
  components: {
    LinkBar,
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
      this.axios.post('/register', { ...this.form, ref: this.$route.query.ref, 'g-recaptcha-response': token }).then((response) => {
        if (response.data.err_result) {
          this.verrors = response.data.err_result;
        } else {
          window.location.href = '/';
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
}
</script>

<style lang="scss" scoped>
.login-card {
  width: 500px;
}
.h-line {
  color: var('--gray');
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
    "continueWithTwitter": "Continue With Twitter"
  },
}
</i18n>
