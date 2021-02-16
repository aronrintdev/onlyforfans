<template>
  <div class="container static-page-layout">
    <b-card class="support-card mt-5" :title="$t('header')">
      <div class="mt-5">
        Please see our <router-link :to="{ name: 'static.faq' }">Frequently Asked Questions</router-link> section for
        more information.
      </div>
    </b-card>

    <div class="mt-auto mb-3">
      <LinkBar />
    </div>
  </div>
</template>

<script>
import LinkBar from './LinkBar'
export default {
  components: {
    LinkBar
  },
}
</script>

<i18n lang="json5">
{
  "en": {
    "header": "Support"
  },
}
</i18n>
