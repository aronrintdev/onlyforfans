<template>
  <div class="wrap">
    <video
      ref="videoPlayer"
      class="video-js vjs-big-play-centered"
      playsInline
      webkit-playsinline="true"
    ></video>
  </div>
</template>

<script>
import videojs from "video.js";
import "video.js/dist/video-js.css";

export default {
  name: "VideoPlayer",
  props: {
    options: {
      type: Object,
      default() {
        return {};
      },
    },
    source: {
      type: Object,
    },
    play: Boolean,
  },
  data() {
    return {
      player: null,
    };
  },
  watch: {
    play(val) {
      if (this.$refs.videoPlayer && this.$refs.videoPlayer.paused) {
        this.$refs.videoPlayer.play();
      } else {
        this.$refs.videoPlayer.pause();
      }
    }
  },
  mounted() {
    const self = this;
    this.player = videojs(
      this.$refs.videoPlayer,
      {
        fluid: true,
        controls: true,
        userActions: {
          doubleClick: false
        }
      },
      () => {
      }
    ).ready(function () {
      this.src({ type: self.source.type, src: self.source.filepath });
      this.load();
    });
  },
  beforeDestroy() {
    if (this.player) {
      this.player.dispose();
    }
  },
};
</script>
<style scoped>
.wrap {
  width: 100%;
  max-width: 80vw;
  margin: auto;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>