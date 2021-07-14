<template>
  <div>
    <div class="form-ctrl d-flex">
      <b-btn
        variant="link"
        class="upload-files"
        v-b-tooltip.hover
        :title="$t('tooltips.uploadFiles')"
        @click="$emit('attachFiles')"
      >
        <fa-icon :icon="['far', 'file-alt']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn
        variant="link"
        class="clickme_to-record_video"
        v-b-tooltip.hover
        :title="$t('tooltips.recordVideo')"
        @click="$emit('recordVideo')"
      >
        <fa-icon :icon="['fas', 'video']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn
        variant="link"
        class="clickme_to-record_audio"
        v-b-tooltip.hover
        :title="$t('tooltips.recordAudio')"
        @click="$emit('recordAudio')"
      >
        <fa-icon :icon="['fas', 'microphone']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn
        variant="link"
        class="clickme_to-select_vault_file"
        v-b-tooltip.hover
        :title="$t('tooltips.vaultSelect')"
        @click="$emit('vaultSelect')"
      >
        <fa-icon :icon="['fas', 'archive']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn
        variant="link"
        class="clickme_to-set_scheduled"
        v-b-tooltip.hover
        :title="$t('tooltips.schedule')"
        :disabled="false"
        @click="$emit('openScheduleMessage')"
      >
        <fa-icon :icon="['far', 'calendar-alt']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn
        variant="link"
        class="clickme_to-set-price"
        v-b-tooltip.hover
        :title="$t('tooltips.setPrice')"
        :disabled="false"
        @click="$emit('setPrice')"
      >
        <fa-icon :icon="['fas', 'dollar-sign']" class="clickable fa-lg" fixed-width />
      </b-btn>
      <b-btn type="submit" variant="primary" class="clickme_to-submit_message ml-auto" :disabled="false">
        {{ $t('send') }}
      </b-btn>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/Footer.vue
 */
import Vuex from 'vuex'

export default {
  name: 'Footer',

  components: {},

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

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
      "schedule": "Schedule Message To Be Sent At",
      "setPrice": "Set Message Unlock Price",
    },
  }
}
</i18n>
