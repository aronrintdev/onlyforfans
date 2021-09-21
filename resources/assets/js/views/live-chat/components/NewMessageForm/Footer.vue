<template>
  <div class="toolbar d-flex flex-wrap align-items-center pb-sm-5" :class="{'d-flex': !mobile}">
    <b-btn
      v-for="item in buttonsFiltered"
      :key="item.key"
      variant="link"
      :disabled="isSending || item.disabled"
      :class="item.class"
      v-b-tooltip.hover.bottom="{boundary: 'viewport', title: item.tooltip}"
      @click="item.onClick"
    >
      <fa-icon
        :icon="item.icon"
        :size="iconSize"
        fixed-width
        :class="item.selected ? 'text-primary' : 'text-secondary'"
      />
    </b-btn>
    <b-btn :disabled="isSending || hasPrice || hasScheduled" variant="success" class="text-nowrap" @click="$emit('addTip')">
      <fa-icon icon="dollar-sign" fixed-width />
      <span class="mr-2">{{ hasTip ? $t('editTip') : $t('addTip') }}</span>
    </b-btn>
    <div class="ml-auto d-flex align-items-end" :class="{'float-right': mobile}">
      <div class="d-flex flex-column">
        <b-btn
          variant="primary"
          class="submit text-nowrap"
          :disabled="isSending"
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
    hasTip: { type: Boolean, default: false },
    hasPrice: { type: Boolean, default: false },
    hasScheduled: { type: Boolean, default: false },
    isSending: { type: Boolean, default: false },
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
          hide: this.isIOS9PlusAndAndroid,
          onClick: (e) => this.$emit('recordVideo', e),
          icon: this.isSelected('recordVideo') ? ['fas', 'video'] : ['far', 'video'],
          selected: this.isSelected('recordVideo'),
          tooltip: this.$t('tooltips.recordVideo'),
        }, {
          key: 'recordAudio',
          class: 'record-audio',
          hide: this.isIOS9PlusAndAndroid,
          onClick: (e) => this.$emit('recordAudio', e),
          icon: this.isSelected('recordAudio') ? ['fas', 'microphone'] : ['far', 'microphone'],
          selected: this.isSelected('recordAudio'),
          tooltip: this.$t('tooltips.recordAudio'),
        }, {
          key: 'vaultSelect',
          class: 'vault-select',
          onClick: (e) => this.$emit('vaultSelect', e),
          icon: this.isSelected('vaultSelect') ? ['fas', 'photo-video'] : ['far', 'photo-video'],
          selected: this.isSelected('vaultSelect'),
          tooltip: this.$t('tooltips.vaultSelect'),
        }, {
          key: 'openScheduleMessage',
          class: 'open-schedule-message',
          disabled: this.hasTip,
          onClick: (e) => this.$emit('openScheduleMessage', e),
          icon: this.isSelected('openScheduleMessage') ? ['fas', 'calendar-alt'] : ['far', 'calendar-alt'],
          selected: this.isSelected('openScheduleMessage'),
          tooltip: this.$t('tooltips.openScheduleMessage'),
        }, {
          key: 'setPrice',
          class: 'set-price',
          disabled: this.hasTip,
          onClick: (e) => this.$emit('setPrice', e),
          icon: this.isSelected('setPrice') ? ['fas', 'tag'] : ['far', 'tag'],
          selected: this.isSelected('setPrice'),
          tooltip: this.$t('tooltips.setPrice'),
        },
      ]
    },

    buttonsFiltered() {
      return _.filter(this.buttons, o => (!o.hide) )
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
  // .tool-items {
  //   overflow-x: auto;
  // }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "send": "Send",
    "tooltips": {
      "uploadFiles": "Add Photo",
      "recordVideo": "Record Video",
      "recordAudio": "Record Audio",
      "vaultSelect": "Add Photo From My Media",
      "openScheduleMessage": "Schedule Message",
      "setPrice": "Set Message Unlock Price",
      "sendWithTip": "Include a tip with your message",
    },
    "addTip": "Add Tip",
    "editTip": "Edit Tip"
  }
}
</i18n>
