<template>
  <b-card :title="$t('title')">
    <transition name="quick-fade" mode="out-in">
      <router-view />
    </transition>
  </b-card>
</template>

<script>
/**
 * Settings component for a user's open subscriptions
 */

export default {
  name: 'MySubscriptions',
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "My Subscriptions"
  }
}
</i18n>
