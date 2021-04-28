<template>
  <div>
    <b-tabs v-model="activeTab" card>
      <b-tab :title="$t('active.tab', { count: count.active })" :disabled="count.active === 0">
        <b-row>
          <b-col
            v-for="item in active"
            :key="item.id"
            class="my-3"
            lg="6"
            xl="4"
          >
            <Card :subscription="item" @details="onDetails" />
          </b-col>
        </b-row>
      </b-tab>

      <b-tab :title="$t('inactive.tab', { count: count.inactive })" :disabled="count.inactive === 0">
        <b-row>
          <b-col
            v-for="item in inactive"
            :key="item.id"
            class="my-3"
            lg="6"
            xl="4"
          >
            <Card :subscription="item" @details="onDetails" />
          </b-col>
        </b-row>
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
/**
 * List display of subscriptions
 */
import Vuex from 'vuex'
import Card from './Card'

export default {
  name: 'SubscriptionsList',

  components: {
    Card,
  },

  props: {
    table: { type: Boolean, default: false },
    tab: { type: Number, default: 0 },
  },

  computed: {
    ...Vuex.mapState('subscriptions', [ 'count', 'active', 'inactive' ])
  },

  data: () => ({
    activeTab: 0,
  }),

  methods: {
    ...Vuex.mapActions('subscriptions', [ 'getActive', 'getInactive' ]),

    onDetails(subscription) {
      this.$router.push({ name: 'subscriptions.details', params: { id: this.$encoder.encode(subscription.id) } })
    },

    load() {
      // Load open subscriptions
      this.getActive({ page: 1 })
      this.getInactive({ page: 1 })
    },

  },

  watch: {
    activeTab(value) {
      if (this.tab !== value) {
        if (value > 0) {
          this.$router.push({ name: 'settings.my-subscriptions', query: { t: value } })
        } else {
          this.$router.push({ name: 'settings.my-subscriptions' })
        }
      }
    },
  },

  mounted() {
    this.activeTab = this.tab
    this.load()
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "active": {
      "tab": "Active ({count})"
    },
    "inactive": {
      "tab": "Inactive ({count})"
    }
  }
}
</i18n>
