<template>
  <b-list-group-item>
    <section v-if="isDateBreak" class="msg-grouping-day-divider">
      <span>{{ moment(value.created_at).format('MMM DD, YYYY') }}</span>
    </section>
    <section class="crate" :class="value.sender_id === session_user.id ? 'session_user' : 'other_user'">
      <article class="box">
        <div v-if="value.attachments" class="attachments">
          <Attachment v-for="item in value.attachments" :key="item.id" :value="item" />
        </div>
        <VueMarkdown v-if="value.mcontent" class="msg-content" :source="value.mcontent || ''" />
        <div class="msg-timestamp">
          {{ moment(value.created_at).format('h:mm A') }}
        </div>
      </article>
    </section>
  </b-list-group-item>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Message.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import Attachment from './Attachment'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  name: 'Message',

  components: {
    Attachment,
    VueMarkdown,
  },

  props: {
    isDateBreak: { type: Boolean, default: false },
    value: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState( ['session_user'] ),
  },

  data: () => ({
    moment: moment,
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
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
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
