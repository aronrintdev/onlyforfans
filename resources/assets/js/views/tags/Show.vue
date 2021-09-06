<template>
  <div class="container-xl tag-page" id="view-show_tag">
    <b-card v-if="!isLoading">
      <b-card-text>
        <h4 class="mb-3 mb-md-4"><fa-icon icon="tag" /> {{ ctag.name ? `#${ctag.name}` : '' }}</h4>

        <b-tabs content-class="mt-3">
          <b-tab active>
            <template #title>
              <div v-if="!mobile">Posts {{ `(${posts.length})` }}</div>
              <div v-else><fa-icon icon="pen" /> {{ `(${posts.length})` }}</div>
            </template>
            <b-row>
              <b-col sm="12" class="my-5 text-center" v-if="!posts.length"><em>There are no posts</em></b-col>
              <b-col class="mb-3" sm="12" lg="6" v-for="post in posts" :key="post.id">
                <PostDisplay :post="post" :session_user="session_user" />
              </b-col>
            </b-row>
          </b-tab>
          <b-tab>
            <template #title>
              <div v-if="!mobile">Mediafiles {{ `(${mediafiles.length})` }}</div>
              <div v-else><fa-icon icon="photo-video" /> {{ `(${mediafiles.length})` }}</div>
            </template>
            <b-row>
              <b-col sm="12" class="my-5 text-center" v-if="!mediafiles.length"><em>There are no mediafiles</em></b-col>
              <b-col class="mb-3" sm="12" lg="4" v-for="mediafile in mediafiles" :key="mediafile.id">
                <div class="media-wrapper">
                  <PreviewFile 
                    :data-mf_id="mediafile.id"
                    :mediafile="mediafile" 
                    @render-lightbox="renderLightbox" 
                    class="tag-file" 
                  />
                </div>
              </b-col>
            </b-row>
          </b-tab>
        </b-tabs>
      </b-card-text>
    </b-card>
    <b-modal v-model="isMediaLightboxModalVisible" id="modal-media-lightbox" centered title="" hide-footer body-class="p-0" size="lg">
      <MediaLightbox 
        @close="isMediaLightboxModalVisible=false" 
        @reload="() => {}"
        context="vault-dashboard" 
        :session_user="session_user" 
        :mediafile="selectedMediafile" />
    </b-modal>
  </div>
</template>

<script>
import Vuex from 'vuex'
import PostDisplay from '@components/posts/Display'
import PreviewFile from '@components/vault/PreviewFile'
import MediaLightbox from '@components/vault/MediaLightbox'

export default {
  components: {
    PostDisplay,
    PreviewFile,
    MediaLightbox,
  },

  created() {
    // Fetch Tag Model
    axios.get(this.$apiRoute('contenttags.show', this.$route.params.id))
      .then(res => {
        this.ctag = res.data;
        this.posts = this.ctag.posts.data || [];
        this.mediafiles = this.ctag.mediafiles || [];
        this.vaultfolders = this.ctag.vaultfolders || [];
      })
  },

  computed: {
    ...Vuex.mapState(['session_user', 'mobile']),
    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({
    ctag: {},
    posts: [],
    mediafiles: [],
    vaultfolders: [],
    isMediaLightboxModalVisible: false,
    selectedMediafile: null,
  }),

  methods: {
    renderLightbox(mediafile) {
      this.selectedMediafile = mediafile
      this.isMediaLightboxModalVisible = true
    },
  },

  watch: {
    $route(val) {
      axios.get(this.$apiRoute('contenttags.show', val.params.id))
        .then(res => {
          this.ctag = res.data;
          this.posts = this.ctag.posts.data || [];
          this.mediafiles = this.ctag.mediafiles || [];
          this.vaultfolders = this.ctag.vaultfolders || [];
        })
    }
  }
   
}
</script>

<style lang="scss">
.media-wrapper .img-box .custom-control {
  display: none;
}

.tag-page {
  .post-crate {
    height: 100%;
    .superbox-post {
      height: 100%;
      .post-crate-content {
        flex: 1;
      }
      .card-footer {
        min-height: 65px;
      }
    }
  }
}
</style>
