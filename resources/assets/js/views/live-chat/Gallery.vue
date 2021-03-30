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
                  <router-link :to="`/messages/${userId}`">
                    <button class="btn" type="button"> 
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                  </router-link>
                  <span class="top-bar-title">Gallery</span>
                </div>
              </div>
              <div class="gallery-content">
                <div class="empty">No media in this conversation yet</div>
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
  export default {
    //
    data: () => ({
      userId: undefined,
    }),
    mounted() {
      this.userId = this.$route.params.id;
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
</style>