<template>
  <section class="crate" :class="value.sender_id === session_user.id ? 'session_user' : 'other_user'">
    <article class="box" :style="{ minWidth: '50%' }">

      <b-card class="locked mb-1 b-none" bg-variant="light" :style="{ minWidth: '50%' }">
        <div v-if="images.length > 0" class="d-flex justify-content-center">
          <b-img-lazy v-if="firstImageBlur" :src="firstImageBlur" />
          <div v-else class="p-5 text-muted">
            <fa-icon icon="lock" size="3x" />
          </div>
        </div>

        <div class="information mb-2 text-muted d-flex justify-content-between">
          <div class="media-information">
            <div v-if="images.length > 0" class="images mr-2 d-inline">
              <fa-icon icon="images" fixed-width />
              <span v-text="images.length" />
            </div>
            <div v-if="videos.length > 0" class="videos mr-2 d-inline">
              <fa-icon icon="video" fixed-width />
              <span v-text="videos.length" />
            </div>
            <div v-if="audios.length > 0" class="audios mr-2 d-inline">
              <fa-icon icon="volume-up" fixed-width />
              <span v-text="audios.length" />
            </div>

          </div>
          <div class="price">
            {{ value.price | niceCurrency }}
            <fa-icon icon="lock" fixed-width />
          </div>
        </div>
        <b-btn variant="primary" block @click="onPurchase">
          {{ $t('purchase') }}
        </b-btn>
      </b-card>

      <VueMarkdown :html="false" v-if="value.mcontent" class="content" :source="value.mcontent || ''" />
      <!-- Message Footer -->
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
      </div>
    </article>
  </section>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Message/Locked
 */
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import moment from 'moment'
import Attachments from './Attachments'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  name: 'Locked',

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

    images() {
      return this.value.attachments.filter(a => a.is_image)
    },

    firstImageBlur() {
      const withBlurs = this.images.filter(a => a.has_blur)
      if (withBlurs.length > 0) {
        return withBlurs.blurFilepath
      }
      return false
    },

    videos() {
      return this.value.attachments.filter(a => a.is_video)
    },

    audios() {
      return this.value.attachments.filter(a => a.is_audio)
    },

    other() {
      return 0
    },
  },

  data: () => ({
    moment: moment,
  }),

  methods: {
    onPurchase() {
      eventBus.$emit('open-modal', { key: 'render-purchase-message', data: { message: this.value } })
    },

  },

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
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "purchase": "Purchase this message",
    "seen": "Seen",
    "tipTimestampTooltip": "This message contains financial transaction information"
  }
}
</i18n>
