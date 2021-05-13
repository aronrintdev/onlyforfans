<template>
  <div class="container static-page-layout">
    <b-card class="support-card mt-5" :title="$t('header')">
      <div class="mt-5">
        Please see our <router-link :to="{ name: 'static.faq' }">Frequently Asked Questions</router-link> section for
        more information.
      </div>
    </b-card>
  </div>
</template>

<i18n lang="json5">
{
  "en": {
    "header": "Support"
  },
}
</i18n>
