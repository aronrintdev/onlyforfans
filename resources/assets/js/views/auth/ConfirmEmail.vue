<template>
  <div class="container w-100 d-flex flex-column mt-5">
    <b-card class="login-card mx-auto">
      <template #header>
        <div class="h2 text-center mb-0" v-text="$t('header')" />
      </template>
      <b-alert variant="success" v-text="$t('message')" show />
      <b-btn variant="primary" block :to="{ name: 'login' }">
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
  name: 'ConfirmEmail',
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
"message": "Thank you for registering your account. Please check your email and use the verification link to verify your email before logging in for the first time. If you did not receive this email, please check your spam folder."
  },
}
</i18n>
