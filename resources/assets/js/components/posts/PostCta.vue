<template>
  <div>
    <template v-if="$options.filters.isSubscriberOnly(post)">
      <article class="locked-content d-flex justify-content-center align-items-center">
        <div class="d-flex flex-column">
          <b-icon icon="lock-fill" font-scale="5" variant="light" />
          <b-button @click="renderSubscribe" class="mt-3" variant="primary">Subscribe</b-button>
        </div>
      </article>
    </template>
    <template v-else>
      <article class="locked-content d-flex justify-content-center align-items-center">
        <div class="d-flex flex-column">
          <b-icon icon="lock-fill" font-scale="5" variant="light" />
          <b-button @click="renderPurchasePost" class="mt-3" variant="primary">Buy</b-button>
        </div>
      </article>
    </template>
  </div>
</template>

<script>
import { eventBus } from '@/app'

export default {
  components: { },

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
    timelineRoute() {
      return {
        name: 'timeline.show',
        params: { slug: this.post.timeline_slug }
      }
    },
  },

  data: () => ({
  }),

  mounted() { },

  created() {},

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

body .locked-content {
  background: url('/images/locked_post.png') center center no-repeat !important;
  background-size: auto;
  background-size: cover !important;
  min-height: 20rem;
}
</style>
