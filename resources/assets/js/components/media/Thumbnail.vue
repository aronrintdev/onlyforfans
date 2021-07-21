<template>
  <div class="cursor-pointer" @click="onClick">
    <img
      v-if="value.is_image"
      :src="value.filepath"
      class="img-thumbnail rounded"
    />
    <div
      v-if="value.is_video"
      class="video img-thumbnail rounded"
    >
      <video>
        <source :src="value.filepath" :type="value.mimetype" />
      </video>
      <fa-icon :icon="['far', 'play-circle']" class="text-white icon-play" />
    </div>
    <vue-plyr
      v-if="value.is_audio"
      class="audio"
    >
      <audio controls playsinline>
        <source :src="value.filepath" type="audio/webm" />
        <source :src="value.filepath" type="audio/mp3" />
        <source :src="value.filepath" type="audio/ogg" />
      </audio>
    </vue-plyr>
  </div>
</template>

<script>
/**
 * Thumbnail Display for a media file
 * resources/assets/js/components/media/Thumbnail.vue
 */
import Vuex from 'vuex'

export default {
  name: 'Thumbnail',

  components: {},

  props: {
    value: { type: Object, default: () => ({})},
  },

  computed: {},

  data: () => ({}),

  methods: {
    onClick(e) {
      if (this.value.is_audio) {
        return
      }
      this.$emit('click', { e, value: this.value })
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.video {
  position: relative;

  &::before {
    content: "";
    display: block;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.2);
    position: absolute;
    top: 0;
    left: 0;
  }

  video {
    width: 100%;
    height: 100%;
  }

  .icon-play {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 34px;
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
