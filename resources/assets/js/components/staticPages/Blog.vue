<template>
  <div class="container static-page-layout">
    <b-card class="blog-card mt-5" :title="$t('header')">
      <div class="mt-5">
        Blog Items
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
    "header": "Blog",
  },
}
</i18n>
