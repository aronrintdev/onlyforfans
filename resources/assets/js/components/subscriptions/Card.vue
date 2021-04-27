<template>
  <Card :value="subscription.for" :access-level="subscription.access_level">
    <div class="d-flex justify-content-between mt-3">
      <div class="h4">
        <b-badge :variant="subscription.active ? 'success' : 'warning'" class="user-select-none">
          <span v-text="$t(`accessLevel.${subscription.access_level}`)" />
          <fa-icon :icon="subscription.active ? 'check' : 'times'" class="ml-2" fixed-width />
        </b-badge>
        <b-badge v-if="subscription.canceled" :variant="'warning'" class="user-select-none">
          <span v-text="$t(`canceled`)" />
          <fa-icon :icon="'times'" class="ml-2" fixed-width />
        </b-badge>
      </div>

      <div class="ml-3">
        <b-btn variant="outline-secondary" @click="onDetails">
          <fa-icon icon="cog" size="lg" fixed-width />
        </b-btn>
      </div>
    </div>
  </Card>
</template>

<script>
/**
 * Card display for subscription
 */

import Card from '@components/lists/Card'

export default {
  name: 'SubscriptionCard',

  components: {
    Card,
  },

  model: {
    prop: 'subscription',
    event: 'change',
  },

  props: {
    subscription: { type: Object, default: () => {} },
  },

  methods: {
    onDetails() {
      this.$emit('details', this.subscription)
    },
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "accessLevel": {
      "premium": "Premium"
    },
    "canceled": "Canceled"
  }
}
</i18n>