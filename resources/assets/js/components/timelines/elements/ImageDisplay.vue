<template>
  <div class="mediafile-crate" v-bind:data-mediafile_guid="mediafile.id">
    <b-card
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-mediafile"
      header-class="d-flex justify-content-between"
      no-body
    >

      <template v-if="mediafile.access">
        <div @click="renderFull" v-if="is_feed" class="p-2 btn">
          <b-icon icon="arrows-angle-expand" variant="primary" font-scale="1.2" />
        </div>
        <img v-if="mediafile.is_image"
          class="d-block"
          :src="(use_mid && mediafile.has_mid) ? mediafile.midFilepath : mediafile.filepath"
          :alt="mediafile.mfname"
        >
      </template>
      <template v-else-if="mediafile.resource_type==='posts'">
        <PostCta :post="mediafile.resource" :session_user="session_user" />
      </template>

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import PostCta from '@components/posts/PostCta'
//import MediaSlider from './MediaSlider'

export default {
  components: {
    PostCta,
    //MediaSlider,
  },

  props: {
    mediafile: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    is_feed: { type: Boolean, default: true }, // is in context of a feed?
  },

  computed: {
    username() {
      return this.post.user.username
    },
    isLoading() {
      return !this.mediafile || !this.session_user
    },
  },

  data: () => ({
  }),

  mounted() { },

  created() {},

  methods: {

    renderFull() {
      if (this.post.access) {
        eventBus.$emit('open-modal', { key: 'show-post', data: { post: this.post } })
      } else {
        if ( this.$options.filters.isSubscriberOnly(this.post) ) {
          eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.timeline } })
        } else if ( this.$options.filters.isPurchaseable(this.post) ) {
          eventBus.$emit('open-modal', { key: 'render-purchase-post', data: { post: this.post } })
        }
      }
    },

    editPost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
    },

    deletePost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
      if (!is) {
        return
      }
      this.$emit('delete-post', this.post.id)
    },
  },

  watch: { },

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
