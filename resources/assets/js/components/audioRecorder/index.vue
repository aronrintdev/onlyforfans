<template>
  <div class="audio-recorder">
    <div class="d-flex justify-content-between align-items-center audio-recorder-header">
      <div class="d-flex align-items-center icon-microphone">
        <fa-icon :icon="['far', 'microphone']" class="text-primary mr-2" />
        <span class="title">{{ $t('title') }}</span>
      </div>
      <b-button variant="outline-primary" class="p-1 border-0 icon-close" @click="closeAudioRec">
        <fa-icon :icon="['fal', 'times']" fixed-width class="text-dark" />
      </b-button>
    </div>
    <div class="text-center duration">
      {{ `${this.currentTime.mins >= 10 ? this.currentTime.mins : '0' + this.currentTime.mins}:${this.currentTime.secs >= 10 ? this.currentTime.secs : '0' + this.currentTime.secs}` }}
    </div>
    <div class="d-flex justify-content-between align-items-center audio-recorder-content">
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
          width="64"
          height="64">
          <circle
            class="progress-meter"
            stroke="#007bff"
            stroke-width="3"
            fill="transparent"
            r="29"
            cx="32"
            cy="32" />
          <circle
            class="progress-value"
            stroke="#007bff"
            stroke-width="3"
            fill="transparent"
            r="29"
            cx="32"
            cy="32" />
        </svg>
        <div class="icon-record" v-if="!isRecording" :disabled="!isDeviceReady" @click="startRec">
          <fa-icon :icon="['far', 'scrubber']" fixed-width class="text-danger icon-rec" />
        </div>
        <div class="icon-record"  v-if="isRecording" @click="stopRec">
          <fa-icon :icon="['fas', 'stop']" fixed-width class="text-primary icon-stop" />
        </div>
      </div>
      <div>&nbsp;</div>
    </div>
    <audio id="myAudio" class="video-js vjs-default-skin invisible"></audio>
  </div>
</template>

<script>
import videojs from 'video.js';
// Required libraries for video record
import RecordRTC from 'recordrtc';
import 'webrtc-adapter';
import WaveSurfer from 'wavesurfer.js';
import MicrophonePlugin from 'wavesurfer.js/dist/plugin/wavesurfer.microphone.js';
WaveSurfer.microphone = MicrophonePlugin;

import videojs_wavesurfer_css from 'videojs-wavesurfer/dist/css/videojs.wavesurfer.css';
import Wavesurfer from 'videojs-wavesurfer/dist/videojs.wavesurfer.js';

import Record from 'videojs-record/dist/videojs.record.js';

export default {
  data: () => ({
    audioRecInterval: null,
    audioDevices: [],
    recLimit: 60,
    player: null,
    isRecording: false,
    isDeviceReady: false,
    currentTime: {
      mins: 0,
      secs: 0
    },
  }),
  computed: {
    formattedRecTime() {
      return `${this.currentTime.mins >= 10 ? this.currentTime.mins : '0' + this.currentTime.mins}:${this.currentTime.secs >= 10 ? this.currentTime.secs : '0' + this.currentTime.secs}`;
    }
  },
  mounted() {
    const options = {
      controls: true,
      bigPlayButton: false,
      width: 600,
      height: 300,
      fluid: false,
      plugins: {
        wavesurfer: {
          backend: 'WebAudio',
          waveColor: 'black',
          progressColor: '#2E732D',
          displayMilliseconds: true,
          debug: true,
          cursorWidth: 1,
          hideScrollbar: true,
          responsive: true,
          plugins: [
              // enable microphone plugin
              WaveSurfer.microphone.create({
                  bufferSize: 4096,
                  numberOfInputChannels: 1,
                  numberOfOutputChannels: 1,
                  constraints: {
                      video: false,
                      audio: true
                  }
              })
          ]
        },
        record: {
          audio: true,
          video: false,
          maxLength: this.recLimit,
          displayMilliseconds: true,
          debug: true
        }
      }
    };

    const self = this;
    this.player = videojs('myAudio', options, function() {
      self.$nextTick(() => {
        self.player.record().getDevice();
      }, 500)
    });
    
    this.player.on('finishRecord', function() {
      self.$emit('complete', self.player.recordedData);
      self.$nextTick(() => {
        self.closeAudioRec();
      });
    });

    this.player.on('deviceReady', function() {
      console.log('------------ device ready;');
      self.player.record().enumerateDevices();
      self.isDeviceReady = true;
    });

    this.player.on('enumerateReady', function() {
      const devices = self.player.record().devices;
      self.audioDevices = devices.filter(device => device.kind === 'audioinput');
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
      const current = self.player.record().streamDuration;
      self.currentTime = {
        mins: parseInt(current / 60, 10),
        secs: parseInt(current, 10) % 60,
      };
      self.setProgress(current / self.recLimit);
    });

    // Close video rec modal when click toast close icon
    this.$root.$on('bv::toast:hidden', (event) => {
      if (event.componentId === 'record-alert') {
        self.closeAudioRec();
      }
    });

    this.setProgress(0);
  },
  methods: {
    closeAudioRec() {
      this.isRecording = false;
      if (this.player) this.player.record().destroy();
      this.$emit('close');
    },
    startRec() {
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
  },
}
</script>

<style lang="scss" scoped>
.audio-recorder {
  position: relative;
  border: 1px solid rgb(85 85 85 / 28%);
  border-radius: 3px;

  .audio-recorder-header {
    padding: 10px;

    .title {
      color: #000;
      font-size: 16px;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 1px;
      margin: 0;
      font-weight: 500;
    }

    .icon-close, .icon-microphone {
      line-height: 1;
      svg {
        width: 20px;
        height: 20px;
      }
    }

    .icon-close:hover svg {
      color: #fff !important;
    }
  }

  .duration {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;

    span {
      width: 40px;
      &:first-child {
        text-align: right;
      }
    }
  }

  .audio-recorder-content {
    padding: 20px;

    .icon-record-wrapper {
      position: relative;
      margin-right: 32px;

      .progress-ring {
        position: absolute;
        z-index: 1;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        .progress-meter {
          opacity: 0.2;
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
        width: 32px;
        height: 32px;
        position: relative;
        z-index: 2;
        cursor: pointer;

        &[disabled] {
          pointer-events: none;
        }

        svg {
          width: 100%;
          height: 100%;

          &.icon-stop {
            width: 60%;
            height: 60%;
            margin: 20%;
          }
        }
      }
    }

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
</style>

<style lang="scss">
  .vue-audio-recorder span {
    display: none;
  }
  .vue-audio-recorder .dropdown-menu {
    bottom: unset;
  }

  .audio-recorder-content {
    .icon-audio button {
      line-height: 1.3;
      font-size: 18px;
      padding: 8px;

      &::after {
        display: none;
      }
    }
  }
  .myAudio-dimensions {
    height: 0;
  }
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "RECORDING AUDIO",
    "warningTitle": "Warning!"
  }
}
</i18n>
