<template>
  <div>
    <div class="form-ctrl d-flex">
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
      <b-btn type="submit" variant="primary" class="submit ml-auto" :disabled="false">
        {{ $t('send') }}
      </b-btn>
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
    ...Vuex.mapState(['screenSize']),

    buttons() {
      return [
        {
          key: 'uploadFiles',
          class: 'upload-files',
          onClick: (e) => this.$emit('attachFiles', e),
          icon: this.isSelected('uploadFiles') ? ['fas', 'file-alt'] : ['far', 'file-alt'],
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
          icon: this.isSelected('setPrice') ? ['fas', 'dollar-sign'] : ['far', 'dollar-sign'],
          selected: this.isSelected('setPrice'),
          tooltip: this.$t('tooltips.setPrice'),
        },
      ]
    },

    iconSize() {
      switch (this.screenSize) {
        case 'xs': case 'sm': case 'md':
          return 'lg'
        case 'lg': case 'xl': default:
          return '2x'
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

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "send": "SEND",
    "tooltips": {
      "uploadFiles": "Attach Files",
      "recordVideo": "Record Video",
      "recordAudio": "Record Audio",
      "vaultSelect": "Attach Files From Your Vault",
      "openScheduleMessage": "Schedule Message To Be Sent At",
      "setPrice": "Set Message Unlock Price",
    },
  }
}
</i18n>
