<template>
  <article class="d-flex">
    <b-btn variant="link" :disabled="processing" class="ml-auto" @click="markAllRead">
      <fa-icon v-if="processing" icon="spinner" spin fixed-width />
      {{ $t('text') }}
    </b-btn>
  </article>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/MarkAllRead.vue
 */
import Vuex from 'vuex'

export default {
  name: 'MarkAllRead',

  components: {},

  props: {},

  computed: {},

  data: () => ({
    processing: false,
  }),

  methods: {
    async markAllRead() {
      this.processing = true
      await axios.post( this.$apiRoute('chatthreads.markAllRead') )

      // reset unread count for all threads
      this.chatthreads.forEach(thread => {
        thread.unread_count = 0
      })

      // reload total unread count
      this.getUnreadMessagesCount()
      this.processing = false
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "text": "Mark All as Read"
  }
}
</i18n>
