<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg">
      </div>
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-body nopadding">
              <div class="top-bar">
                <div>
                  <router-link :to="`/messages/${$route.params.id}`">
                    <button class="btn" type="button"> 
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                  </router-link>
                  <span class="top-bar-title">Gallery</span>
                </div>
              </div>
              <div class="gallery-content" v-if="!mediafiles.length">
                <div class="empty">No media in this conversation yet</div>
              </div>
              <div class="gallery-list" v-if="mediafiles.length"> 
                <div class="img-wrapper" v-for="media in mediafiles" :key="media.id">
                  <img v-preview:scope-a v-if="media.is_image" :src="media.filepath" :alt="media.mfname" />
                  <video v-if="media.is_video" @click="() => showMediaPopup(media)">
                    <source :src="media.filepath" type="video/mp4" />
                  </video>
                  <svg v-if="media.is_video" class="video-play-svg" viewBox="0 0 142.448 142.448" style="enable-background:new 0 0 142.448 142.448;">
                    <g>
                      <path d="M142.411,68.9C141.216,31.48,110.968,1.233,73.549,0.038c-20.361-0.646-39.41,7.104-53.488,21.639
                        C6.527,35.65-0.584,54.071,0.038,73.549c1.194,37.419,31.442,67.667,68.861,68.861c0.779,0.025,1.551,0.037,2.325,0.037
                        c19.454,0,37.624-7.698,51.163-21.676C135.921,106.799,143.033,88.377,142.411,68.9z M111.613,110.336
                        c-10.688,11.035-25.032,17.112-40.389,17.112c-0.614,0-1.228-0.01-1.847-0.029c-29.532-0.943-53.404-24.815-54.348-54.348
                        c-0.491-15.382,5.122-29.928,15.806-40.958c10.688-11.035,25.032-17.112,40.389-17.112c0.614,0,1.228,0.01,1.847,0.029
                        c29.532,0.943,53.404,24.815,54.348,54.348C127.91,84.76,122.296,99.306,111.613,110.336z"/>
                      <path d="M94.585,67.086L63.001,44.44c-3.369-2.416-8.059-0.008-8.059,4.138v45.293
                        c0,4.146,4.69,6.554,8.059,4.138l31.583-22.647C97.418,73.331,97.418,69.118,94.585,67.086z"/>
                    </g>
                  </svg>
                  <img v-if="media.mimetype.indexOf('audio/') > -1" src="/images/audio-thumb.png" alt="" @click="showMediaPopup(media)" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <b-modal modal-class="media-modal" hide-header centered hide-footer ref="media-modal" title="Video/Audio Popup">
      <div class="video-modal" v-if="popupMedia && popupMedia.is_video">
        <video controls autoplay>
          <source :src="popupMedia.filepath" type="video/mp4" />
        </video>
      </div>
      <div class="audio-modal" v-if="popupMedia && popupMedia.mimetype.indexOf('audio/') > -1">
        <audio controls autoplay>
          <source :src="popupMedia.filepath" type="audio/mpeg" />
        </audio>
      </div>
    </b-modal>
  </div>
</template>

<script>
  /**
   * Messages Gallery View
   */
  import PhotoSwipe from 'photoswipe/dist/photoswipe';
  import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default';
  import createPreviewDirective from 'vue-photoswipe-directive';

  require('../../../static/images/audio-thumb.png');

  const options = {
    showAnimationDuration: 0,
    bgOpacity: 0.75
  };

  export default {
    //
    data: () => ({
      mediafiles: [],
      popupMedia: undefined,
    }),
    directives: {
      preview: createPreviewDirective(options, PhotoSwipe, PhotoSwipeUI),
    },
    mounted() {
      this.axios.get(`/chat-messages/${this.$route.params.id}/mediafiles`)
        .then((res) => {
          this.mediafiles = res.data;
        });
    },
    methods: {
      showMediaPopup: function(media) {
        this.popupMedia = media;
        this.$refs['media-modal'].show();
      },
      closeMediaPopup: function() {
        this.popupMedia = undefined;
        this.$refs['media-modal'].hide();
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/gallery.scss";
</style>
