<template>
  <div v-if="!isLoading" class="feed-crate tag-posts tag-crate">
  <div class="tag-debug">
    <ul>
      <li>Timeline ID: {{ timeline.id | niceGuid }}</li>
      <li>Price: {{ timeline.price }}</li>
      <li>Slug: {{ timeline.slug }}</li>
    </ul>
  </div>
    <section class="row">
      <div class="w-100">
        <article
          v-for="(feedItem, index) in renderedItems"
          :key="feedItem.id"
          class="col-sm-12 mb-3"
          v-observe-visibility="index === renderedItems.length - 1 ? endPostVisible : false"
        >
          <div class="tag-debug">INDEX: {{ index }}</div>
          <!-- for now we assume posts; eventually need to convert to a DTO (ie more generic 'feedItem') : GraphQL ? -->
          <PostDisplay
            :post="feedItem"
            :session_user="session_user"
            @delete-post="deletePost"
          />
        </article>
        <article class="load-more-item col-sm-12 mb-3">
          <b-card :class="{ 'cursor-pointer': !moreLoading && !isLastPage }" @click="onLoadMoreClick">
            <div class="w-100 d-flex my-3 justify-content-center" >
              <fa-icon v-if="moreLoading" icon="spinner" spin size="lg" />
              <span v-else-if="isLastPage">End Of Content</span>
              <span v-else>Load More</span>
            </div>
          </b-card>
        </article>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import PostDisplay from '@components/posts/Display'

export default {
  components: {
    PostDisplay,
  },

  props: {
    is_homefeed: null,
    session_user: null,
    timeline: null,
  },

  computed: {
    ...Vuex.mapState(['feeddata']), // should include keys: data (posts) and meta (pagination info), and links 
    ...Vuex.mapState(['unshifted_timeline_post']),

    isLoading() {
      return !this.feeddata || !this.session_user || !this.timeline
    },

    username() { // feed owner
      return this.timeline.username
    },
    timelineId() {
      return this.timeline.id
    },

    currentPage() {
      return this.feeddata.meta.current_page
    },
    nextPage() {
      return this.feeddata.meta.current_page + 1
    },
    lastPage() {
      return this.feeddata.meta.last_page
    },
    isLastPage() {
      return this.feeddata.meta.current_page === this.feeddata.meta.last_page
    },
  },

  data: () => ({
    renderedItems: [], // this will likely only be posts
    renderedPages: [], // track so we don't re-load same page (set of posts) more than 1x
    lastPostVisible: false,
    moreLoading: true,
    limit: 5, // %FIXME: un-hardcode
  }),

  mounted() {
    // window.addEventListener('scroll', this.onScroll)
  },
  beforeDestroy() {
    // window.removeEventListener('scroll', this.onScroll)
  },

  created() {
    this.$store.dispatch('getFeeddata', { timelineId: this.timelineId, page: 1, limit: this.limit, isHomefeed: this.is_homefeed })

    eventBus.$on('update-post', postId => {
      console.log('components.timelines.PostFeed - eventBus.$on(update-post)')
      this.updatePost(postId) 
    })

    eventBus.$on('update-feed', () => {
      console.log('components.timelines.PostFeed - eventBus.$on(update-feed)')
      this.renderedPages = []
      this.renderedItems = []
      this.lastPostVisible = false
      this.moreLoading = true
      this.$store.dispatch('getFeeddata', { timelineId: this.timelineId, page: 1, limit: this.limit, isHomefeed: this.is_homefeed })
    })
  },

  methods: {

    endPostVisible(isVisible) {
      this.lastPostVisible = isVisible
      if (isVisible && !this.moreLoading && !this.isLastPage) {
        this.loadMore()
      }
    },

    onLoadMoreClick() {
      if (!this.moreLoading && !this.isLastPage) {
        this.loadMore()
      }
    },

    onScroll(e) {
      const atBottom = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
      if (atBottom && !this.isLoading) {
        this.loadMore()
      }
    },

    /*
    renderPurchasePostModal(post) {
      // v-b-modal.modal-purchase_post
      this.$bvModal.show('modal-purchase_post', post)
    },

    renderSubscribeModal() {
      this.$bvModal.show('modal-purchase_post')
    }
     */

    // re-render a single post (element of renderedItems) based on updated data, for example after purchase
    // %TODO: should this update the element in vuex feeddata instead (?)
    // see: 
    //   https://vuejs.org/v2/guide/list.html#Array-Change-Detection
    //   https://vuejs.org/v2/guide/reactivity.html#For-Arrays
    async updatePost(postId) {
      const response = await axios.get( route('posts.show', postId) );
      const idx = this.renderedItems.findIndex( ri => ri.id === postId )
      //this.renderedItems[idx] = response.data.data
      this.$set(this.renderedItems, idx, response.data.data)
      //console.log('updatePost', response)
    },

    async deletePost(postId) {
      const url = `/posts/${postId}`
      const response = await axios.delete(url)
      this.renderedPages = []
      this.renderedItems = []
      this.$store.dispatch('getFeeddata', { timelineId: this.timelineId, page: 1, limit: this.limit, isHomefeed: this.is_homefeed })
    },

    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadMore() {
      if ( !this.moreLoading && !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.moreLoading = true;
        this.$log.debug('loadMore', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.$store.dispatch('getFeeddata', { timelineId: this.timelineId, page: this.nextPage, limit: this.limit, isHomefeed: this.is_homefeed })
      }
    },

  },

  watch: {
    unshifted_timeline_post (newVal, oldVal) {
      this.$log.debug('PostFeed - watch:unshifted_timeline_post', { newVal, oldVal })
      this.renderedItems.pop(); // pop the 'oldest' to keep pagination offset correct
      this.renderedItems.unshift(newVal)
    },

    feeddata (newVal, oldVal) {
      if ( !this.renderedPages.includes(newVal.meta.current_page) ) {
        this.renderedPages.push(newVal.meta.current_page)
        this.renderedItems = this.renderedItems.concat(newVal.data) // the actual posts
        this.moreLoading = false
      }
    },
  },
}
</script>

<style scoped>
.tag-debug {
  display: none;
}
</style>
