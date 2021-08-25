<template>
  <div class="toolbar" :class="{'d-flex': !mobile}">
    <div class="tool-items d-flex flex-shrink-1 py-2 mr-3">
      <b-btn
        v-for="item in buttons"
        :key="item.key"
        variant="link"
        :disabled="item.disabled"
        :class="item.class"
        v-b-tooltip.hover
        :title="item.tooltip"
        @click="item.onClick"
      >
        <fa-icon
          :icon="item.icon"
          :size="iconSize"
          fixed-width
          :class="item.selected ? 'text-primary' : 'text-secondary'"
        />
      </b-btn>
    </div>
    <div class="py-2 ml-auto d-flex align-items-end" :class="{'float-right': mobile}">
      <b-btn
        variant="success"
        class="mr-3 text-nowrap"
        v-b-tooltip.hover="mobile ? null :$t('tooltips.sendWithTip')"
      >
        <fa-icon icon="dollar-sign" class="mr-2" />
        <span>{{ $t('sendWithTip') }}</span>
      </b-btn>
      <div class="d-flex flex-column">
        <div
          v-if="!mobile"
          class="font-size-smaller text-muted text-right mr-2"
          v-text="$t('sendHint')"
        />
        <b-btn
          variant="primary"
          class="submit text-nowrap"
          :disabled="false"
          @click="$emit('submit')"
        >
          <span>{{ $t('send') }}</span>
          <fa-icon icon="arrow-right" class="ml-2" />
        </b-btn>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/Footer.vue
 */
import _ from 'lodash'
import Vuex from 'vuex'
import { isAndroid, isIOS, osVersion } from 'mobile-device-detect';

export default {
  name: 'Footer',

  components: {},

  props: {
    selected: { type: Array, default: () => ([]) },
  },

  computed: {
    ...Vuex.mapState(['screenSize', 'mobile']),

    buttons() {
      return [
        {
          key: 'uploadFiles',
          class: 'upload-files',
          onClick: (e) => this.$emit('attachFiles', e),
          icon: this.isSelected('uploadFiles') ? ['fas', 'image'] : ['far', 'image'],
          selected: this.isSelected('uploadFiles'),
          tooltip: this.$t('tooltips.uploadFiles'),
        }, {
          key: 'recordVideo',
          class: 'record-video',
          disabled: this.isIOS9PlusAndAndroid,
          onClick: (e) => this.$emit('recordVideo', e),
          icon: this.isSelected('recordVideo') ? ['fas', 'video'] : ['far', 'video'],
          selected: this.isSelected('recordVideo'),
          tooltip: this.$t('tooltips.recordVideo'),
        }, {
          key: 'recordAudio',
          class: 'record-audio',
          onClick: (e) => this.$emit('recordAudio', e),
          icon: this.isSelected('recordAudio') ? ['fas', 'microphone'] : ['far', 'microphone'],
          selected: this.isSelected('recordAudio'),
          tooltip: this.$t('tooltips.recordAudio'),
        }, {
          key: 'vaultSelect',
          class: 'vault-select',
          onClick: (e) => this.$emit('vaultSelect', e),
          icon: this.isSelected('vaultSelect') ? ['fas', 'archive'] : ['far', 'archive'],
          selected: this.isSelected('vaultSelect'),
          tooltip: this.$t('tooltips.vaultSelect'),
        }, {
          key: 'openScheduleMessage',
          class: 'open-schedule-message',
          onClick: (e) => this.$emit('openScheduleMessage', e),
          icon: this.isSelected('openScheduleMessage') ? ['fas', 'calendar-alt'] : ['far', 'calendar-alt'],
          selected: this.isSelected('openScheduleMessage'),
          tooltip: this.$t('tooltips.openScheduleMessage'),
        }, {
          key: 'setPrice',
          class: 'set-price',
          onClick: (e) => this.$emit('setPrice', e),
          icon: this.isSelected('setPrice') ? ['fas', 'tag'] : ['far', 'tag'],
          selected: this.isSelected('setPrice'),
          tooltip: this.$t('tooltips.setPrice'),
        },
      ]
    },

    iconSize() {
      switch (this.screenSize) {
        case 'xs': case 'sm': case 'md': case 'lg':
          return 'lg'
        case 'xl': default:
          return 'lg'
      }
    },

    isIOS9PlusAndAndroid() {
      return (isIOS && parseInt(osVersion.split('.')[0]) >= 9) || isAndroid;
    }
  },

  data: () => ({}),

  methods: {
    isSelected(key) {
      return _.indexOf(this.selected, key) > -1
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.toolbar {
  align-items: end;
  .tool-items {
    overflow-x: auto;
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "sendHint": "Ctrl + Enter",
    "send": "Send",
    "tooltips": {
      "uploadFiles": "Add Photo",
      "recordVideo": "Record Video",
      "recordAudio": "Record Audio",
      "vaultSelect": "Add Photo From My Media",
      "openScheduleMessage": "Schedule Message To Be Sent At",
      "setPrice": "Set Message Unlock Price",
      "sendWithTip": "Include a tip with your message",
    },
    "sendWithTip": "Send with Tip",
  }
}
</i18n>
