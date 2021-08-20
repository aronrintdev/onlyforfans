<template>
  <div v-if="!isLoading" class="mediafile-crate" v-bind:data-mediafile_guid="mediafile.id">
    <b-card
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-mediafile"
      header-class="d-flex justify-content-between"
      no-body
    >

      <template v-if="mediafile.access">
        <div class="image-preview" @click="renderFull" v-if="mediafile.is_image" >
          <b-img-lazy 
            class="d-block"
            :src="(use_mid && mediafile.has_mid) ? mediafile.midFilepath : mediafile.filepath"
            :alt="mediafile.mfname" />
        </div>
        <MediaSlider v-else-if="mediafile.is_video" 
          @click="renderFull"
          :mediafiles="[mediafile]" 
          :session_user="session_user" 
          :use_mid="use_mid" />
      </template>
      <template v-else-if="mediafile.resource_type==='posts'">
        <PostCta :post="mediafile.resource" :session_user="session_user" :primary_mediafile="mediafile" />
      </template>

      <!-- <template footer>
        <div class="panel-footer fans">

          <div class="d-flex justify-content-between">
            <ul class="d-flex list-inline footer-ctrl mb-0">
              <li class="mr-3">
                <LikesButton @toggled="toggleLike()" :filled="isLikedByMe" :count="likeCount" />
              </li>
            </ul>
            <ul class="d-flex list-inline footer-ctrl mb-0">
              <li class="mr-3">
                <span @click="toggleFavorite()" class="tag-clickable">
                  <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
                  <fa-icon v-else fixed-width :icon="['far', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
                </span>
              </li>
            </ul>
          </div>

          <div class="like-count">
            <template v-if="likeCount===1"><span class="mr-2">{{ likeCount }} like</span></template>
            <template v-if="likeCount > 1"><span class="mr-2">{{ likeCount }} likes</span></template>
          </div>

        </div>
      </template> -->

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import PostCta from '@components/posts/PostCta'
import LikesButton from '@components/common/LikesButton'
import MediaSlider from '@components/posts/MediaSlider'

export default {
  props: {
    mediafile: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    is_feed: { type: Boolean, default: true }, // is in context of a feed?
  },

  computed: {
    isLoading() {
      return !this.mediafile || !this.session_user
    },
  },

  data: () => ({
    isLikedByMe: false,
    likeCount: 0, // %FIXME INIT
    isFavoritedByMe: false,
  }),

  methods: {

    toggleLike() {
    },

    async toggleFavorite() { // was toggleBookmark
      let response
      if (this.isFavoritedByMe) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'mediafiles', // 'photos' ?
          favoritable_id: this.mediafile.id,
        })
        this.isFavoritedByMe = false
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'mediafiles',
          favoritable_id: this.mediafile.id,
        })
        this.isFavoritedByMe = true
      }
    },

    async renderFull() {
      const response = await axios.get( route('posts.show', this.mediafile.resource_id) );
      const post = response.data.data
      const imageIndex = post.mediafiles.filter(file => !file.is_audio).findIndex(file => file.id === this.mediafile.id)
      if (post.access) {
        eventBus.$emit('open-modal', { key: 'show-post', data: { post, imageIndex } })
      } else {
        if ( this.$options.filters.isSubscriberOnly(post) ) {
          eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.timeline } })
        } else if ( this.$options.filters.isPurchaseable(post) ) {
          eventBus.$emit('open-modal', { key: 'render-purchase-post', data: { post } })
        }
      }
    },

  },

  components: {
    PostCta,
    MediaSlider,
    LikesButton,
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
.image-preview {
  position: relative;
  width: 100%;
  height: 0;
  padding-top: 100%;
  background: transparent;
}

.image-preview img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
