<template>
  <section class="messages">
    <Message
      v-for="(item, idx) in items"
      :key="item.id"
      :value="item"
      :isDateBreak="isDateBreak(idx)"
      :timeline="timeline"
      @unsend="unsend"
      @update="onMessageUpdate"
      v-observe-visibility="idx === items.length - 1 ? endVisible : false"
    />
    <b-list-group-item class="load-more" v-observe-visibility="endVisible"> </b-list-group-item>
    <b-list-group-item v-if="isLastPage">
      <section class="msg-grouping-day-divider">
        <span v-text="$t('startOfThread')" />
      </section>
    </b-list-group-item>
    <div class="mt-auto"> </div>
    <div v-if="items.length === 0" class="d-flex w-100 h-100 align-items-center justify-content-center">
      <div v-if="loading" class="h4 d-flex flex-column justify-content-center align-items-center">
        <fa-icon icon="spinner" spin class="mb-3" />
        {{ $t('loading') }}
      </div>
      <div v-else class="h4" v-text="isSearch ? $t('noSearchResults', {query: searchQuery}) : $t('noResults')" />
    </div>
  </section>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/MessageDisplay.vue
 */
import Vuex from 'vuex'
import moment from 'moment'

import Message from './Message'

export default {
  name: 'MessageDisplay',

  components: {
    Message,
  },

  props: {
    isLastPage: { type: Boolean, default: false },
    isSearch: { type: Boolean, default: false },
    items: { type: Array, default: () => ([]) },
    loading: { type: Boolean, default: false },
    searchQuery: { type: String, default: '' },
    timeline: { type: Object, default: () => ({}) },
  },

  computed: {},

  data: () => ({}),

  methods: {
    endVisible(isVisible) {
      this.$emit('endVisible', isVisible) // %NOTE: semantically: "is end (n) visible", *not* "end (v) visible" (noun not verb)
    },

    isDateBreak(idx) {
      if (idx === this.items.length - 1) {
        return true
      }
      const current = moment(this.items[idx].delivered_at)
      const next = moment(this.items[idx + 1].delivered_at)
      return !current.isSame(next, 'date')
    },

    onMessageUpdate(message) {
      this.$emit('updateMessage', message)
    },

    unsend(data) { // note, server call to delete message is done elsewhere
      const index = this.items.findIndex(t => t.id === data.id)
      this.items.splice(index, 1)
      this.$forceUpdate()
    }
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.messages {
  width: 100%;
  overflow-y: auto;
  display: flex;
  flex-direction: column-reverse;

  .list-group-item {

    border: none;
    padding: 0.5rem 0.50rem;
    //padding: 0.5rem 1.25rem;

    .crate {
      display: flex;
      max-width: 75%;

      .box {
        .msg-content {
          margin-left: auto;
          background: rgba(218,237,255,.53);
          border-radius: 5px;
          padding: 9px 12px;
          color: #1a1a1a;
        }
        .msg-timestamp {
          font-size: 11px;
          color: #8a96a3;
          text-align: right;
        }

      } // box
    } // crate

  }

  .msg-grouping-day-divider {
    font-size: 11px;
    line-height: 15px;
    text-align: center;
    color: #8a96a3;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    span {
      padding: 0 10px;
    }
    &:after, &:before {
      content: '';
      display: block;
      flex: 1;
      height: 1px;
      background: rgba(138,150,163,.2);
    }
  }

}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "loading": "Loading",
    "noSearchResults": "No results for \"{query}\"",
    "noResults": "No messages in this thread",
    "startOfThread": "Beginning of Conversation"
  }
}
</i18n>
