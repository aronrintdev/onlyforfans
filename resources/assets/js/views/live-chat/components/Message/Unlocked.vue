<template>
  <section class="crate" :class="[ value.sender_id === session_user.id ? 'session_user' : 'other_user', { mobile }]">
    <article class="box">
      <Attachments :attachments="value.attachments" />
      <VueMarkdown :html="false" v-if="value.mcontent" class="content" :source="value.mcontent || ''" />
      <div
        class="timestamp d-flex align-items-center"
        :class="value.sender_id === session_user.id ? 'flex-row-reverse' : 'flex-row'"
      >
        <div class="mx-1">
          {{ moment(value.delivered_at).format('h:mm A') }}
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
        <span v-if="value.is_sender && value.purchase_only">
          {{ value.price | niceCurrency }}
          <span v-if="value.purchased_by.length > 0">
            Purchased
          </span>
          <span v-else>
            Not yet Purchased
          </span>
        </span>
      </div>
    </article>
  </section>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Message/Unlocked.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import Attachments from './Attachments'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  name: 'Unlocked',

  components: {
    Attachments,
    VueMarkdown,
  },

  props: {
    isDateBreak: { type: Boolean, default: false },
    value: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState( ['session_user', 'mobile'] ),

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
    &.mobile {
      padding-left: 1rem;
    }

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
    &.mobile {
      padding-right: 1rem;
    }

    .content {
      margin-right: auto;
    }
    .timestamp {
      text-align: left;
    }
  }



} // crate
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "seen": "Seen",
    "tipTimestampTooltip": "This message contains financial transaction information"
  }
}
</i18n>
