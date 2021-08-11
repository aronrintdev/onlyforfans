<template>
  <div>
    <div class="img-box position-relative">
      <b-img-lazy
        @click="$emit('render-lightbox', mediafile)"
        v-if="mediafile.is_image"
        :class="mediafile.selected ? 'tag-selected' : ''"
        :src="mediafile.filepath"
      />
      <div
        @click="$emit('render-lightbox', mediafile)"
        :class="mediafile.selected ? 'tag-selected video' : 'video'"
        v-if="mediafile.is_video"
      >
        <video>
          <source :src="`${mediafile.filepath}#t=2`" :type="mediafile.mimetype" />
        </video>
        <div class="icon-video">
          <fa-icon :icon="['fas', 'play']" class="text-white icon-play" />
        </div>
      </div>
      <div
        @click="$emit('render-lightbox', mediafile)"
        v-if="mediafile.is_audio"
        :class="mediafile.selected ? 'tag-selected audio' : 'audio'"
      >
        <fa-icon :icon="['fas', 'file-audio']" />
      </div>
      <div class="render-date">
        <p class="m-0">{{  moment(mediafile.created_at).format('MMMM D') }}</p>
      </div>
      <div class="select-file">
        <b-form-checkbox ref="checkbox" size="lg" :checked="mediafile.selected" :value="true" @change="onSelect" />
      </div>
    </div>
    <span>{{ mediafile.orig_filename }}</span>
  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'

export default {
  model: {
    prop: 'mediafile',
  },

  props: {
    mediafile: { type: Object, default: () => ({ mediafile: {} })},
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      return !this.session_user || !this.mediafile
    },

  },

  data: () => ({
    moment: moment,
  }), // data

  created() { },

  mounted() { },

  methods: {
    onClicked() {
      console.log('onClicked')
      this.onSelect(!this.mediafile.selected)
    },

    onSelect(value) {
      console.log('onSelect')
      if (value) {
        this.$emit('input', { ...this.mediafile, selected: true })
      } else {
        this.$emit('input', { ...this.mediafile, selected: false })
      }
    },
  }, // methods


  watch: { }, // watch

  components: { },

}
</script>

<style lang="scss" scoped>
.video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;

  &::before {
    content: "";
    display: block;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
    position: absolute;
    top: 0;
    left: 0;
  }

  video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .icon-video {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 10px 9px 10px 11px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.5);
    transition: background-color 0.1s ease;
    width: 36px;
    height: 36px;
  }

  .icon-play {
    display: block;
    width: 100%;
    height: 100%;
  }

  &:hover .icon-video {
    background: #007bff;
  }
}

.audio {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(#00000025,rgba(138,150,163,0), #00000010);

  &.tag-selected::before {
    content: '';
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
  }

  svg {
    width: 50px;
    height: 50px;
    color: rgba(0, 0, 0, 0.5);
  }

  &:hover {
    svg {
      color: #007bff;
    }
  }
}
</style>
