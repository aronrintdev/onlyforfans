<template>
  <b-list-group-item class="message" v-if="shown">
    <section v-if="isDateBreak" class="grouping-day-divider">
      <span>{{ moment(value.created_at).format('MMM DD, YYYY') }}</span>
    </section>
    <section class="crate" :class="value.sender_id === session_user.id ? 'session_user' : 'other_user'">
      <article class="box">
        <Attachments :attachments="value.attachments" />
        <VueMarkdown v-if="value.mcontent" class="content" :source="value.mcontent || ''" />
        <div
          class="timestamp d-flex align-items-center"
          :class="value.sender_id === session_user.id ? 'flex-row-reverse' : 'flex-row'"
        >
          <div class="mx-1">
            {{ moment(value.created_at).format('h:mm A') }}
          </div>
          <span
            v-if="value.attachments && value.attachments.length > 0 && value.attachments[0].type === 'tip'"
            class="mx-1"
            v-b-tooltip.hover
            :title="$t('tipTimestampTooltip')"
          >
            <fa-icon icon="dollar-sign" fixed-width />
          </span>
          <span v-if="value.is_read" v-b-tooltip.hover :title="$t('seen')" class="mx-1">
            <fa-icon icon="check" fixed-width />
          </span>
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
import Attachments from './Attachments'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  name: 'Message',

  components: {
    Attachments,
    VueMarkdown,
  },

  props: {
    isDateBreak: { type: Boolean, default: false },
    value: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState( ['session_user'] ),

    shown() {
      return this.value.mcontent || this.value.attachments.length > 0
    },
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
.message {
    border: none;
    padding: 0.5rem 1.25rem;

  .crate {
    display: flex;
    max-width: 100%;

    .box {
      max-width: 100%;
      display: flex;
      flex-direction: column;
      .content {
        width: auto;
        background: rgba(218,237,255,.53);
        border-radius: 5px;
        padding: 9px 12px;
        color: #1a1a1a;
      }
      .timestamp {
        font-size: 11px;
        color: #8a96a3;
        text-align: right;
      }

    } // box

    &.session_user {
      justify-content: flex-end;
      margin-left: auto;
      margin-right: 0;
      padding-left: 5rem;

      .content {
        margin-left: auto;
      }
      .timestamp {
        text-align: right;
      }
    }

    &.other_user {
      justify-content: flex-start;
      margin-left: 0;
      margin-right: auto;
      padding-right: 5rem;
      .content {
        margin-right: auto;
      }
      .timestamp {
        text-align: left;
      }
  }

  } // crate
}

.content ::v-deep p {
  margin-bottom: 0;
}

.grouping-day-divider {
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
  "en": {
    "seen": "Seen",
    "tipTimestampTooltip": "This message contains financial transaction information"
  }
}
</i18n>
