<template>
  <div>
    <template v-if="$options.filters.isSubscriberOnly(post)">
      <article :style="backgroundImg" class="locked-content d-flex justify-content-center align-items-center h-100">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
          <fa-icon icon="lock" class="text-light" size="5x" />
          <b-button @click="renderSubscribe" class="mt-3" variant="primary">Subscribe</b-button>
        </div>
      </article>
    </template>
    <template v-else>
      <article :style="backgroundImg" class="locked-content d-flex position-relative justify-content-center align-items-center h-100">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
          <fa-icon icon="lock" class="text-light" size="5x" />
          <!--
          <b-button @click="renderPurchasePost" class="mt-3" variant="primary">Unlock Post for {{ post.price_display || (post.price | niceCurrency) }}</b-button>
          -->
          <b-button @click="renderPurchasePost" class="mt-3" variant="primary">Unlock Post for {{ post.price | niceCurrency }}</b-button>
          <div v-if="post.mediafile_count" class="mediafile-count text-white position-absolute">
            <fa-icon icon="images" class="d-inline my-auto" /> {{ post.mediafile_count }}
          </div>
        </div>
      </article>
    </template>
  </div>
</template>

<script>
/* CTA: "Call-To-Action" */

import { eventBus } from '@/eventBus'

export default {
  components: { },

  props: {
    post: null,
    session_user: null,
    primary_mediafile: null,
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
    /*
    timelineRoute() {
      return {
        name: 'timeline.show',
        params: { slug: this.post.timeline_slug }
      }
    },
     */

    /*
    hasMediafiles() {
      return this.post.mediafiles?.length > 0
    },

    primaryMediafile() {
      return this.hasMediafiles ? this.post.mediafiles[0] : null
    },
     */

    backgroundImg() {
      if (this.primary_mediafile && this.primary_mediafile.has_blur) {
        return {
          '--background-image': `url(${this.primary_mediafile.blurFilepath})`,
        }
      } else {
        return {
          '--background-image': `url(/images/locked_post.png)`,
        }
      }
    },
  },

  data: () => ({
  }),

  mounted() { },
  created() { },

  methods: {
    renderPurchasePost() {
      eventBus.$emit('open-modal', {
        key: 'render-purchase-post', 
        data: {
          post: this.post,
        }
      })
    },

    renderSubscribe() {
      eventBus.$emit('open-modal', {
        key: 'render-subscribe', 
        data: {
          timeline: this.post.timeline, // .postable_id
        }
      })
    },
  },

  watch: { }
}
</script>

<style scoped>
ul {
  margin: 0;
}

.mediafile-count {
  bottom: 0.5rem;
  right: 1rem;
}
.locked-content {
  background-image: var(--background-image);
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover !important;
  min-height: 20rem;
}
</style>
