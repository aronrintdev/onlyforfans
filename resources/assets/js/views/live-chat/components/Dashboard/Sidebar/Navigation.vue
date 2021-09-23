<template>
  <article class="top-bar d-flex justify-content-between align-items-center mb-3">
    <div class="h4" v-text="$t('header')" />
    <div class="d-flex">
      <b-btn variant="link" class="clickme_to-view_mass_mgs_analytics" :to="linkMassMsgAnalytics">
        <fa-icon :icon="['fas', 'chart-bar']" class="fa-lg" />
      </b-btn>
      <b-btn variant="link" disabled class="clickme_to-schedule_message" @click="scheduleMessage">
        <fa-icon :icon="['far', 'calendar-alt']" class="fa-lg" />
      </b-btn>
      <b-btn variant="link" @click="$emit('onSearchIconClick')">
        <fa-icon :icon="'search'" :class="searchIconClass" size="lg" />
      </b-btn>
      <b-btn variant="link" class="clickme_to-send_message" :to="linkCreateThread">
        <fa-icon :icon="['fas', 'plus']" size="lg" />
      </b-btn>
    </div>
  </article>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/Navigation.vue
 */
import Vuex from 'vuex'

export default {
  name: 'Navigation',

  components: {},

  props: {
    enableSearch: { type: Boolean, default: false },
  },

  computed: {
    linkCreateThread() {
      return { name: 'chatthreads.create' }
    },
    linkMassMsgAnalytics() {
      return { name: 'chatthreads.stats' }
    },
  },

  data: () => ({
    searchIconClass: 'text-secondary',
  }),

  methods: {
    // TODO: Schedule Message Implementation
    // Disabled button for now.
    scheduleMessage() {},
  },

  watch: {
    enableSearch(value) {
      if (value) {
        this.searchIconClass = 'text-primary'
      } else {
        this.searchIconClass = 'text-secondary'
      }
    }
  },

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "header": "Messages",
  }
}
</i18n>
