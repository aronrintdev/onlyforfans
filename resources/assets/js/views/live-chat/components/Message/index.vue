<template>
  <div>
    <b-list-group-item class="message" :class="{ mobile }" v-if="shown">
      <section v-if="isDateBreak" class="grouping-day-divider">
        <span>{{ moment(value.delivered_at).format('MMM DD, YYYY') }}</span>
      </section>

      <Unlocked v-if="!value.purchase_only || value.is_sender || havePurchased()" :value="value" @onUnsend="showConfirmModal(true)" />
      <Locked v-else :value="value" @onUnsend="showConfirmModal" />

    </b-list-group-item>

    <b-modal id="confirm-undsend" v-model="isConfirmModalVisible" :title="$t('unsend.title')" :centered="mobile">
      <div v-text="modalDescription" />
      <template #modal-footer>
        <b-button v-if="isConfirmModal" variant="primary" @click="onUnsendClicked">Confirm</b-button>
        <b-button @click="hideConfirmModal">Cancel</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Message.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import Attachments from './Attachments'
import Locked from './Locked'
import Unlocked from './Unlocked'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

export default {
  name: 'Message',

  components: {
    Attachments,
    Locked,
    Unlocked,
    VueMarkdown,
  },

  props: {
    isDateBreak: { type: Boolean, default: false },
    value: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState( ['session_user', 'mobile'] ),

    shown() {
      return this.value.mcontent || ( Array.isArray(this.value.attachments) && this.value.attachments.length > 0 )
    },

    modalDescription() {
      if (this.isConfirmModal) {
        return this.$t('unsend.description')
      } else {
        return this.$t('unsend.unableDescription')
      }
    }
  },

  data: () => ({
    moment: moment,
    isConfirmModal: true,
    isConfirmModalVisible: false,
  }),

  methods: {
    // If session_user has purchased this message
    havePurchased() {
      return _.indexOf(this.value.purchased_by, u => u.id === this.session_user.id) > -1
    },

    showConfirmModal(isConfirmModal) {
      this.isConfirmModal = isConfirmModal
      this.isConfirmModalVisible = true
    },

    hideConfirmModal() {
      this.isConfirmModalVisible = false
    },

    onUnsendClicked() {
      this.isConfirmModalVisible = false
      axios.delete(route('chatmessages.destroy', { id: this.value.id }))
        .then(() => this.$emit('unsend', { id: this.value.id }))
        .catch(err => this.showConfirmModal(false))
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.message {
    border: none;
    padding: 0.5rem 1.25rem;
    .crate {
      width: 80%;
    }
}

::v-deep p {
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
    "tipTimestampTooltip": "This message contains financial transaction information",
    "unsend": {
      "title": "Unsend this message",
      "description": "Are you sure you want to unsend this message?",
      "unableDescription": "Can't delete this message",
    }
  }
}
</i18n>
