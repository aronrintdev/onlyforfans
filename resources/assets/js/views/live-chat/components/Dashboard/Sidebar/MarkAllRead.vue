<template>
  <div>
    <article class="d-flex">
      <b-btn variant="link" :disabled="processing" class="ml-auto" @click="confirmMarkAllRead">
        <fa-icon v-if="processing" icon="spinner" spin fixed-width />
        {{ $t('text') }}
      </b-btn>
    </article>
    <b-modal id="confirm-make-all" v-model="isConfirmModalVisible" :title="$t('text')">
      <div class="my-2 text-left" v-text="$t('confirmation.message')" />
      <template #modal-footer>
        <b-button variant="primary" @click="markAllRead">Confirm</b-button>
        <b-button @click="hideConfirmModal">Cancel</b-button>
      </template>
    </b-modal>
  </div>
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
    isConfirmModalVisible: false,
  }),

  methods: {
    ...Vuex.mapActions(['getUnreadMessagesCount']),

    confirmMarkAllRead() {
      this.isConfirmModalVisible = true
    },

    hideConfirmModal() {
      this.isConfirmModalVisible = false
    },

    async markAllRead() {
      this.hideConfirmModal()
      this.processing = true
      await axios.post( this.$apiRoute('chatthreads.markAllRead') )

      this.$emit('updateThreadsAllRead')

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
    "text": "Mark All as Read",
    "confirmation": {
      "message": "Are you sure you want to mark all messages as read?",
    },
  }
}
</i18n>
