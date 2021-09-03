<template>
  <section class="messages">
    <Message
      v-for="(item, idx) in items"
      :key="item.id"
      :value="item"
      :isDateBreak="isDateBreak(idx)"
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
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.messages {
  height: 100%;
  width: 100%;
  overflow-y: auto;
  display: flex;
  flex-direction: column-reverse;

  .list-group-item {

      border: none;
      padding: 0.5rem 1.25rem;

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

    .crate.session_user {
        justify-content: flex-end;
        margin-left: auto;
        margin-right: 0;
    }

    .crate.other_user {
        justify-content: flex-start;
        margin-left: 0;
        margin-right: auto;
    }

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
