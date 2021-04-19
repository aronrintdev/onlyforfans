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
