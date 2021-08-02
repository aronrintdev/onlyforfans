<template>
  <b-list-group-item class="message" v-if="shown">
    <section v-if="isDateBreak" class="grouping-day-divider">
      <span>{{ moment(value.created_at).format('MMM DD, YYYY') }}</span>
    </section>

    <Unlocked v-if="!value.purchase_only || value.is_sender || havePurchased()" :value="value" />
    <Locked v-else :value="value" />

  </b-list-group-item>
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
    ...Vuex.mapState( ['session_user'] ),

    shown() {
      return this.value.mcontent || this.value.attachments.length > 0
    },
  },

  data: () => ({
    moment: moment,
  }),

  methods: {
    // If session_user has purchased this message
    havePurchased() {
      return _.indexOf(this.value.purchased_by, u => u.id === this.session_user.id) > -1
    }
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.message {
    border: none;
    padding: 0.5rem 1.25rem;
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
    "tipTimestampTooltip": "This message contains financial transaction information"
  }
}
</i18n>
