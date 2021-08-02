<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="login-card mx-auto" no-body>
      <template #header>
        <div class="h2 text-center" v-text="$t('header')" />
        <div class="text-center">
          <router-link :to="{ name: 'login' }" v-text="$t('returnToLogin')" />
        </div>
      </template>
      <b-alert variant="success" v-text="$t('message')" show />
      <b-btn variant="primary">
        {{ $t('returnToLogin') }}
      </b-btn>
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
  name: 'EmailVerified',
  components: {
    LinkBar,
  },
  data: () => ({}),

  methods: {},

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
    "header": "Thank You For Registering",
    "returnToLogin": "Return to Login",
    "button": "Login Now",
    "message": "Your email has been successfully verified. You may now log in."
  },
}
</i18n>
