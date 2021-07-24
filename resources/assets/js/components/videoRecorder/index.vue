<template>
  <div class="video-rec-wrapper">
    <div class="d-flex justify-content-between align-items-center video-rec-top-bar">
      <div class="p-1 border-0 icon-video">
        <fa-icon :icon="['far', 'video']" fixed-width class="text-white" />
      </div>
      <h4>{{ $t('title') }}</h4>
      <b-button variant="outline-primary" class="p-1 border-0 icon-close" @click="closeVideoRec">
        <fa-icon :icon="['fal', 'times']" fixed-width class="text-white" />
      </b-button>
    </div>
    <div class="d-flex justify-content-between align-items-end video-rec-bottom-bar">
      <div class="text-center video-duration" v-if="isRecording">
        {{ currentTime.mins >= 10 ? currentTime.mins : `0${currentTime.mins}` }}:
        {{ currentTime.secs >= 10 ? currentTime.secs : `0${currentTime.secs}` }}
      </div>
      <b-dropdown
        id="audio-input-devices"
        dropup
        text="Drop-Up"
        :disabled="audioDevices.length < 2"
        variant="secondary"
        class="border-0 icon-audio"
      >
        <template #button-content>
          <fa-icon :icon="['far', 'microphone']" fixed-width class="text-white" />
        </template>
        <b-dropdown-item @click="changeAudioInput(undefined)">Disable Microphone</b-dropdown-item>
        <b-dropdown-item
          v-for="(device, index) in audioDevices"
          :key="index"
          @click="changeAudioInput(device.deviceId)"
        >
          {{ device.label }}
        </b-dropdown-item>
      </b-dropdown>
      
      <div class="icon-record-wrapper">
        <svg
          class="progress-ring"
          width="70"
          height="70">
          <circle
            class="progress-meter"
            stroke="white"
            stroke-width="3"
            fill="transparent"
            r="32"
            cx="35"
            cy="35" />
          <circle
            class="progress-value"
            stroke="white"
            stroke-width="3"
            fill="transparent"
            r="32"
            cx="35"
            cy="35" />
        </svg>
        <div class="icon-record" v-if="!isRecording" :disabled="!isDeviceReady" @click="startVideoRec">
          <fa-icon :icon="['far', 'scrubber']" fixed-width class="text-danger icon-rec" />
        </div>
        <div class="icon-record"  v-if="isRecording" @click="stopRec">
          <fa-icon :icon="['fas', 'stop']" fixed-width class="text-primary icon-stop" />
        </div>
      </div>
      <b-dropdown
        id="video-input-devices"
        dropup
        text="Drop-Up"
        variant="secondary"
        :disabled="videoDevices.length < 2"
        class="border-0 icon-video"
      >
        <template #button-content>
          <fa-icon :icon="['far', 'video']" fixed-width class="text-white" />
        </template>
        <b-dropdown-item
          v-for="(device, index) in videoDevices"
          :key="index"
          @click="changeVideoInput(device.deviceId)"
        >
          {{ device.label }}
        </b-dropdown-item>
      </b-dropdown>
    </div>
    <video id="myVideo" playsinline class="video-js"></video>
  </div>
</template>

<script>
import videojs from 'video.js';
// Required libraries for video record
import RecordRTC from 'recordrtc';
import Record from 'videojs-record/dist/videojs.record.js';
import { eventBus } from '@/eventBus'

export default {
  data: () => ({
    player: null,
    isRecording: false,
    currentTime: {
      mins: 0,
      secs: 0
    },
    videoRecLimit: 600,
    audioDevices: [],
    videoDevices: [],
    isDeviceReady: false,
  }),
  mounted() {
    // Initialize video recorder
    const options = {
      controls: false,
      fluid: true,
      bigPlayButton: false,
      controlBar: {
        volumePanel: false
      },
      plugins: {
        record: {
          audio: true,
          video: true,
          maxLength: this.videoRecLimit, // 15mins
          displayMilliseconds: true,
          debug: true,
        }
      }
    };
    this.player = videojs('myVideo', options, function() {});
    // check video/audio input devices
    this.player.record().getDevice(); 

    const self = this;
    this.player.on('finishRecord', function() {
      self.$emit('complete', self.player.recordedData);
      self.isRecording = false;
      setTimeout(() => {
        self.closeVideoRec();
      }, 500);
    });

    this.player.one('deviceReady', function() {
      self.player.record().enumerateDevices();
      self.isDeviceReady = true;
    });

    this.player.on('enumerateReady', function() {
      const devices = self.player.record().devices;
      self.audioDevices = devices.filter(device => device.kind === 'audioinput');
      self.videoDevices = devices.filter(device => device.kind === 'videoinput');
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

    this.player.on('progressRecord', function(e) {
      const current = self.player.record().getCurrentTime();
      self.currentTime = {
        mins: parseInt(current / 60, 10),
        secs: parseInt(current, 10) % 60,
      };
      self.setProgress(current / self.videoRecLimit);
    });

    // Close video rec modal when click toast close icon
    this.$root.$on('bv::toast:hidden', (event) => {
      if (event.componentId === 'record-alert') {
        self.closeVideoRec();
      }
    });

    // Init progress bar of record icon
    this.setProgress(0);
  },
  methods: {
    closeVideoRec() {
      if (this.player) this.player.record().destroy();
      this.$emit('close');
    },
    startVideoRec() {
      this.player.record().start();
      this.isRecording = true;
    },
    stopRec() {
      this.player.record().stop();
      this.isRecording = false;
    },
    setProgress(percent) {
      const radius = parseInt($('.progress-value').attr('r'), 10);
      const circumference = radius * 2 * Math.PI;
      const offset = circumference - percent * circumference;
      $('.progress-value').css({
        strokeDasharray: `${circumference} ${circumference}`,
        strokeDashoffset: `${offset}`,
      });
    },
    changeAudioInput(deviceId) {
      this.player.record().setAudioInput(deviceId);
    },
    changeVideoInput(deviceId) {
      this.player.record().setVideoInput(deviceId);
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

  &::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background: rgba(0,0,0,.5) linear-gradient(rgba(0,0,0,.4),rgba(0,0,0,.24) 23%,rgba(0,0,0,.12) 42%,rgba(0,0,0,.06) 56%,rgba(0,0,0,.05) 63%,rgba(0,0,0,.06) 70%,rgba(0,0,0,.12) 84%,rgba(0,0,0,.24));
    z-index: 1;
    pointer-events: none;
  }

  .video-rec-top-bar {
    position: absolute;
    top: 15px;
    left: 20px;
    right: 20px;
    z-index: 3;
 
    h4 {
      color: #fff;
      font-size: 18px;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 1px;
      margin: 0;
    }

    .icon-close, .icon-video {
      line-height: 1;
      svg {
        width: 24px;
        height: 24px;
      }
    }
  }

 .video-rec-bottom-bar {
    position: absolute;
    left: 120px;
    right: 120px;
    z-index: 3;
    bottom: 25px;
  
    .icon-record-wrapper {
      position: relative;

      .progress-ring {
        position: absolute;
        z-index: 1;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        .progress-meter {
          opacity: 0.3;
        }
        .progress-value {
          transition: 0.35s stroke-dashoffset;
          transform: rotate(-90deg);
          transform-origin: 50% 50%;
          opacity: 0;
          stroke-linecap: round;
        
          &[style] {
            opacity: 1;
          }
        }
      }
      .icon-record {
        position: relative;
        z-index: 2;
        width: 40px;
        height: 40px;
        line-height: 1;
        cursor: pointer;
        transition: all 0.3s ease;

        &[disabled] {
          pointer-events: none;
        }

        .icon-rec {
          width: 100%;
          height: 100%;

          &:hover {
            transform: scale(1.1);
            opacity: 0.9;
          }
        }

        .icon-stop {
          width: 50%;
          height: 50%;
          margin: 25%;
        }

      }
    }
    .icon-video button,
    .icon-audio button {
      line-height: 1.3;
      font-size: 22px;
      padding: 10px;

      &::after {
        display: none;
      }
    }
  }
  
}

.video-duration {
  position: absolute;
  top: -50px;
  width: 100%;
  color: #fff;
  font-size: 20px;
}
</style>

<style>
.myVideo-dimensions.vjs-fluid {
  padding-top: 100vh;
}

#audio-input-devices .dropdown-menu,
#video-input-devices .dropdown-menu {
  bottom: unset;
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
