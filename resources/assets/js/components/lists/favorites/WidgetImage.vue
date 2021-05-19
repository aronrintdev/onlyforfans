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
        <img v-if="mediafile.is_image" @click="renderFull"
          class="d-block"
          :src="(use_mid && mediafile.has_mid) ? mediafile.midFilepath : mediafile.filepath"
          :alt="mediafile.mfname"
        >
      </template>
      <template v-else-if="mediafile.resource_type==='posts'">
        <PostCta :post="mediafile.resource" :session_user="session_user" :primary_mediafile="mediafile" />
      </template>

      <template footer>
        <div class="panel-footer fans">

          <div class="d-flex justify-content-between">
            <ul class="d-flex list-inline footer-ctrl mb-0">
                <!--
              <li class="mr-3">
                <LikesButton @toggled="toggleLike()" :filled="isLikedByMe" :count="likeCount" />
              </li>
                -->
            </ul>
            <ul class="d-flex list-inline footer-ctrl mb-0">
                <!--
              <li class="mr-3">
                <span @click="toggleFavorite()" class="tag-clickable">
                  <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
                  <fa-icon v-else fixed-width :icon="['far', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
                </span>
              </li>
                -->
            </ul>
          </div>

          <div class="like-count">
            <template v-if="likeCount===1"><span class="mr-2">{{ likeCount }} like</span></template>
            <template v-if="likeCount > 1"><span class="mr-2">{{ likeCount }} likes</span></template>
          </div>

        </div>
      </template>

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import PostCta from '@components/posts/PostCta'

export default {
  components: {
    PostCta,
  },

  props: {
    mediafile: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    is_feed: { type: Boolean, default: false }, // is in context of a feed?
  },

  computed: {
    isLoading() {
      return !this.mediafile || !this.session_user
    },
  },

  data: () => ({
    isLikedByMe: false,
    likeCount: 0, // %FIXME INIT
    //isFavoritedByMe: false,
  }),

  methods: {

    toggleLike() {
    },

    renderFull() {
      // %FIXME: currently hardcoded for mediafiles that belong to *posts*
      if (this.mediafile.access) {
        eventBus.$emit('open-modal', { key: 'show-photo', data: { mediafile: this.mediafile } })
      } else {
        if ( this.$options.filters.isSubscriberOnly(this.mediafile.resource) ) {
          eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.timeline } })
        } else if ( this.$options.filters.isPurchaseable(this.mediafile.resource) ) {
          eventBus.$emit('open-modal', { key: 'render-purchase-post', data: { post: this.mediafile.resource } })
        }
      }
    },

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
