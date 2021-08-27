<template>
  <div v-if="!isLoading" class="preview_upgrade-crate tag-crate my-3 d-none d-md-block">
    <b-card tag="article">
      <b-card-text>
        <b-row class="mx-0">
          <h3 class="card-title mb-2 px-1">Photos</h3>
        </b-row>

        <b-row class="mx-0">
          <b-col cols="4" v-for="(p,idx) in previewposts" :key="p.id" class="px-1">
  <div class="tag-debug">
            <ul class="pl-0">
              <li>ID: {{ p.id | niceGuid }}</li>
              <li>Type: {{ p.type | enumPostType }}</li>
              <li>Price: {{ p.price }}</li>
              <li>Access: {{ p.access }}</li>
            </ul>
  </div>
            <article class="tag-wrap mb-3">
              <div v-if="!p.access" @click="renderPost(p)" :style="backgroundImg(p)" class="locked-content tag-thumb"></div>
              <fa-icon v-if="!p.access" @click="renderPost(p)" class="tag-icon text-light" icon="lock" size="2x" />
              <div v-if="p.access" @click="renderPost(p)" :style="backgroundImg(p)" class="tag-thumb"></div>
            </article>
          </b-col>
          <div v-if="previewposts.length === 0" class="p text-center text-secondary w-100">No photos available</div>
        </b-row>
        <div v-if="previewposts.length > 5" class="view-more-photos text-primary text-right px-1" @click="viewMorePhotos('photos')">View more photos</div>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/eventBus'

export default {

  props: {
    session_user: null,
    timeline: null,
    viewMorePhotos: { type: Function },
  },

  computed: {
    ...Vuex.mapGetters([
      'previewposts',
    ]),

    isLoading() {
      return !this.session_user || !this.previewposts || !this.timeline
    }
  },

  data: () => ({
    limit: 6,
    allViewed: false,
  }),

  created() { 
    eventBus.$on('update-posts', postId => {
      this.updatePost(postId) 
    })
  },

  mounted() { 
    this.$store.dispatch('getPreviewposts', { timelineId: this.timeline.id, limit: this.limit })
  },

  methods: { 
    renderPost(p) {
      if (p.access) {
        eventBus.$emit('open-modal', { key: 'show-post', data: { post: p } })
      } else {
        if ( this.$options.filters.isSubscriberOnly(p) ) {
          eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.timeline } })
        } else if ( this.$options.filters.isPurchaseable(p) ) {
          eventBus.$emit('open-modal', { key: 'render-purchase-post', data: { post: p } })
        }
      }
    },

    async updatePost(postId) {
      const response = await axios.get( route('posts.show', postId) ); // %TODO: this can be moved to store, as also done in feed
      const idx = this.previewposts.findIndex( ri => ri.id === postId )
      this.$set(this.previewposts, idx, response.data.data)
    },

    renderSubscribe() {
      console.log('FollowCtrl.renderSubscribe() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: this.timeline,
        }
      })
    },

    backgroundImg(post) {
      const mediafiles = post.mediafiles.filter(mf => mf.is_image);
      if ( post.access ) {
        if ( mediafiles && mediafiles[0] && mediafiles[0].has_thumb ) {
          return { '--background-image': `url(${mediafiles[0].thumbFilepath})` }
        }
        if ( mediafiles && mediafiles[0] && mediafiles[0].has_mid ) {
          return { '--background-image': `url(${mediafiles[0].midFilepath})` }
        }
        return { '--background-image': `url(${mediafiles[0].filepath})` }
      } else { // locked content 
        if ( mediafiles && mediafiles[0] && mediafiles[0].has_blur ) {
          return { '--background-image': `url(${mediafiles[0].blurFilepath})` }
        } 
        return { '--background-image': `url(/images/locked_post.png)` }
      }
    },
  },

  components: { },

  watch: {
    previewposts(value, oldVal) {
      if (value.length < 6) this.allViewed = true
      else if (oldVal && value.length - oldVal.length >= 0 && value.length - oldVal.length < 6 && value[0].id === oldVal[0].id) this.allViewed = true
    },
  },
}
</script>

<style scoped>
.preview_upgrade-crate h3 {
  font-size: 20px;
}
.view-more-photos {
  cursor: pointer;
}
.tag-wrap {
  position: relative;
}
/*
.blurred {
  filter: blur(4px);
}
 */
.locked-content {
}
.tag-thumb {
  width: 100%;
  height: 90px;
  background-image: var(--background-image);
  background-size: cover;
}
.tag-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.tag-thumb, .tag-icon {
  cursor: pointer;
}
.tag-debug * {
  font-size: 10px;
  display: none;
}
</style>
