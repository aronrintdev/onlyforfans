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
                  <img v-preview:scope-a :src="media.filepath" alt="" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  /**
   * Messages Gallery View
   */
  import PhotoSwipe from 'photoswipe/dist/photoswipe';
  import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default';
  import createPreviewDirective from 'vue-photoswipe-directive';

  const options = {
    showAnimationDuration: 0,
    bgOpacity: 0.75
  };

  export default {
    //
    data: () => ({
      mediafiles: [],
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
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/home.scss";
  .gallery-content {
    height: calc(100vh - 210px);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .gallery-content .empty {
    font-size: 18px;
    font-weight: 500;
    color: rgba(138,150,163,.7);
    margin: auto;
  }
  .gallery-list {
    min-height: calc(100vh - 210px);
    display: flex;
    flex-flow: row wrap;

    .img-wrapper {
      overflow: hidden !important;
      width: calc(25% - 2px);
      padding-top: calc(25% - 2px);
      height: 0;
      position: relative;
      border: 1px solid transparent;

      img {
        object-fit: cover;
        width: 100%;
        height: auto;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
      }
    }
  }
</style>