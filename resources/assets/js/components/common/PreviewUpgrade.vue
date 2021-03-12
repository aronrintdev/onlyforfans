<template>
  <div v-if="!isLoading" class="preview_upgrade-crate tag-crate">
    <b-card tag="article">
      <b-card-text>
        <b-row>
          <b-col cols="4" v-for="(p,idx) in previewposts.data" :key="p.id" class="px-1">
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
  },

  methods: { 
    renderPost(p) {
      if (p.access) {
        eventBus.$emit('open-modal', { key: 'show-post', data: { post: p } })
      } else {
        eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.timeline } })
      }
    },

    getImage(p) {
      return p.mediafiles[0].filepath
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
</style>
