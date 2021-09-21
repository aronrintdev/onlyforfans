<template>
  <div>
    <b-dropdown class="filter-controls" variant="link" size="sm" left no-caret>
      <template #button-content>
        <span class="mark-button">Mark</span>
        <fa-icon :icon="['fas', 'caret-down']" class="fa-lg" />
      </template>

      <b-dropdown-item @click="confirmMarkAllRead" >
        <span class="ml-3">{{ $t('mark.read') }}</span>
      </b-dropdown-item>
    </b-dropdown>
    <b-modal id="confirm-make-all" v-model="isConfirmModalVisible" :title="$t('mark.read')" :centered="mobile">
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
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/MarkControl.vue
 */
import Vuex from 'vuex'

export default {
  name: 'MarkControl',

  components: {},

  props: {},

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
  },

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

<style lang="scss" scoped>
.filter-controls {
  ::v-deep .dropdown-item {
    padding-left: 0;
  }
}
.mark-button {
  font-size: 16px;
  font-weight: 500;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "mark": {
      "read": "Mark All as Read",
    },
    "confirmation": {
      "message": "Are you sure you want to mark all messages as read?",
    },
  }
}
</i18n>
