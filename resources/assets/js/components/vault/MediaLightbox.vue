<template>
  <div v-if="!isLoading" class="mediafile-crate" v-bind:data-mediafile_guid="mediafile.id">
    <b-card
      no-body
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-mediafile"
      header-class="d-flex justify-content-between"
    >

      <template v-if="true || mediafile.access">
        <b-row>
          <b-col cols="12" class="d-flex align-items-center justify-content-center">
            <div class="w-100">
              <b-img v-if="mediafile.is_image" 
                fluid
                class="d-block w-100"
                :src="(use_mid && mediafile.has_mid) ? mediafile.midFilepath : mediafile.filepath"
                :alt="mediafile.mfname">
              </b-img>
              <MediaSlider v-else-if="!mediafile.is_image" 
                :mediafiles="[mediafile]" 
                :session_user="session_user" 
                :use_mid="use_mid" />
            </div>
          </b-col>
        </b-row>
      </template>

      <template footer>
        <div class="panel-footer">
        </div>
      </template>

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
//import { eventBus } from '@/eventBus'
import MediaSlider from '@components/posts/MediaSlider'

export default {
  props: {
    mediafile: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  computed: {
    isLoading() {
      return !this.mediafile || !this.session_user
    },
  },

  data: () => ({
    stats: null,
  }),

  methods: {
  },

  created() {
    this.axios.get(this.$apiRoute('mediafiles.diskStats', this.mediafile.id)).then(response => {
      this.stats = response.data.stats
    })
  },

  components: {
    MediaSlider,
  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

.feed-crate .superbox-post .card-text {
  color: #383838;
  white-space: no-wrap;
  overflow: hidden;
  max-height: 18rem;
  text-overflow: ellipsis;

  display: -webkit-box;
  -webkit-line-clamp: 5;
  -webkit-box-orient: vertical;
}

.user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}

.user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}

.user-details ul > li .username {
  text-transform: capitalize;
}

.user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
.user-details ul > li:last-child {
  font-size: 14px;
}
</style>
