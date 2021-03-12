<template>
  <div v-if="!isLoading" class="preview_upgrade-crate tag-crate">
    <b-card tag="article">
      <b-card-text>
        <b-row>
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
              <div @click="renderPost(p)" :style="{ backgroundImage: 'url(' + getImage(p) + ')' }" :class="{'blurred': !p.access}" class="tag-thumb"></div>
              <b-icon v-if="!p.access" @click="renderPost(p)" class="tag-icon" icon="lock-fill" font-scale="2" variant="light" />
            </article>
          </b-col>
        </b-row>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/app'

export default {

  props: {
    session_user: null,
    timeline: null,
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
  }),

  created() { 
    this.$store.dispatch('getPreviewposts', { timelineId: this.timeline.id, limit: this.limit })

    eventBus.$on('update-post', postId => {
      console.log('components.common.PreviewUpgrade - eventBus.$on(update-post)')
      this.updatePost(postId) 
    })
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
          timeline_id: this.timeline.id,
        }
      })
    },

    getImage(p) {
      return p.mediafiles[0].filepath
    },

  },

  components: { },
}
</script>

<style scoped>
.tag-wrap {
  border: solid 4px #a5a5a5;
  position: relative;
}
.blurred {
  filter: blur(4px);
}
.tag-thumb {
  width: 90px;
  height: 90px;
  background-size: cover;
}
.tag-icon {
  position: absolute;
  top: 30px;
  left: 30px;
}
.tag-thumb, .tag-icon {
  cursor: pointer;
}
.tag-debug * {
  font-size: 10px;
}
</style>
