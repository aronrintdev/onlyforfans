<template>
  <article class="chatthread-list position-relative flex-grow-1" :class="{ 'mobile': mobile }">
    <b-list-group>
      <Thread
        v-for="thread in threads"
        :key="thread.id"
        :to="linkChatthread(thread.id)"
        :active="isActiveThread(thread.id)"
        :data-ct_id="thread.id"
        class="pr-2 pl-0"
        :participant="participants(thread)"
        :chatthread="thread"
      />
      <b-list-group-item v-if="noThreads && !loading" class="text-center">
        {{ showSearchResults ? $t('no-items-search', { query: searchQuery }) : $t('no-items') }}
      </b-list-group-item>
    </b-list-group>

    <b-pagination
      v-if="showPagination"
      :value="page"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="threads-list"
      @input="page => $emit('pageChange', page)"
      @page-click="pageClickHandler"
      class="mt-3"
    />

    <LoadingOverlay :loading="loading" />
  </article>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Dashboard/Sidebar/ThreadList.vue
 */
import Vuex from 'vuex'

import Thread from './Thread'
import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: 'ThreadList',

  components: {
    LoadingOverlay,
    Thread,
  },

  props: {
    loading: { type: Boolean, default: false },
    page: { type: Number, default: 1 },
    perPage: { type: Number, default: 10 },
    totalRows: { type: Number, default: 0 },
    threads: { type: Array, default: () => ([]) },
    activeContactId: { type: String, default: null },

    showSearchResults: { type: Boolean, default: false },
    searchResults: { type: Array, default: () => ([]) },
    searchQuery: { type: String, default: '' },
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'mobile' ]),

    activeThreadId() {
      return this.$route.params.id
    },

    linkCreateThread() {
      return { name: 'chatthreads.create' }
    },

    noThreads() {
      return this.threads.length === 0
    },
  },

  data: () => ({
    showPagination: false,
  }),

  methods: {

    isActiveThread(id) {
      return id === this.activeThreadId
    },
    isActiveContact(id) {
      return id === this.activeContactId
    },

    linkChatthread(id) {
      return { name: 'chatthreads.show', params: { id: id } }
    },

    pageClickHandler(e, page) {
      this.$emit('pageChange', page)
    },

    participants(thread) { // other than session user
      // _.find because right now we only support 1-on-1 conversations (no group chats)
      if (!thread.participants) {
        return null
      }
      const user = _.find(thread.participants, participant => participant.id !== this.session_user.id)
      return {
        ...user,
        timeline: thread.timeline,
      }
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.chatthread-list {
  overflow: auto;
  &.mobile {
    margin-right: -1rem;
  }
  .list-group-item {
    border-radius: inherit;
    border: none;
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "no-items": "No Chat Threads",
    "no-items-search": "No Chat Threads Found for \"{query}\"",
  }
}
</i18n>
