<template>
  <div class="video-rec-wrapper">
    <h4>{{ $t('title') }}</h4>
    <video id="myVideo" playsinline class="video-js vjs-default-skin"></video>
    <div class="icon-close" @click="closeVideoRec">
      <fa-icon size="lg" :icon="['far', 'times']" class="text-white" />
    </div>
  </div>
</template>

<script>
import videojs from 'video.js';

// Required libraries for video record
import RecordRTC from 'recordrtc';
import Record from 'videojs-record/dist/videojs.record.js';
import TsEBMLEngine from 'videojs-record/dist/plugins/videojs.record.ts-ebml.js';


export default {
  data: () => ({
    player: null,
  }),
  mounted() {
    const options = {
      controls: true,
      fluid: true,
      bigPlayButton: true,
      controlBar: {
        volumePanel: true
      },
      plugins: {
        record: {
          audio: true,
          video: true,
          maxLength: 10,
          displayMilliseconds: true,
          debug: true,
          convertEngine: 'ts-ebml'
        }
      }
    };
    this.player = videojs('myVideo', options, function() {});
    // check video/audio input devices
    this.player.record().getDevice(); 

    const self = this;
    this.player.on('finishRecord', function() {
      self.sortableMedias.push({
        src: URL.createObjectURL(self.player.recordedData),
        file: self.player.recordedData,
        type: 'video/mp4',
      });
    });
    this.player.on('deviceError', function(e) {
      const errorMsg = self.player.deviceErrorCode.message;
      self.$root.$bvToast.toast(errorMsg, {
        variant: 'danger',
        title: self.$t('warningTitle'),
        id: 'record-alert',
        solid: true,
        toaster: 'b-toaster-top-center',
      });
    });
    this.$root.$on('bv::toast:hidden', (event) => {
      if (event.componentId === 'record-alert') {
        self.closeVideoRec();
      }
    });
  },
  beforeDestroy() {
    if (this.player) this.player.record().destroy();
  },
  methods: {
    closeVideoRec() {
      this.$emit('close');
    }
  },
}
</script>

<style lang="scss">
.video-rec-wrapper {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
  z-index: 1050;

  h4 {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: #fff;
    font-size: 20px;
    z-index: 1;
    padding: 6px 15px;
    border-radius: 5px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
  }

  .icon-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 24px;
    height: 24px;
    fill: #fff;
    cursor: pointer;
  }
}
</style>

<style>
.myVideo-dimensions.vjs-fluid {
  padding-top: 100vh;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Record Video",
    "warningTitle": "Warning!"
  }
}
</i18n>
