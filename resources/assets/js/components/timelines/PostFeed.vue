<template>
  <div v-if="!isLoading" class="feed-crate tag-posts tag-crate">
    <div class="tag-debug">
      <ul>
        <li>Timeline ID: {{ timeline.id | niceGuid }}</li>
        <li>Price: {{ timeline.price }}</li>
        <li>Slug: {{ timeline.slug }}</li>
      </ul>
    </div>

    <b-row>
      <b-col>
        <section class="feed-ctrl my-3 p-2 d-flex justify-content-end">
          <div @click="toggleGridLayout" class="btn">
            <b-icon icon="grid" scale="1.2" variant="primary"></b-icon>
          </div>
          <b-dropdown no-caret ref="feedCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
            <template #button-content>
              <b-icon icon="filter" scale="1.5" variant="primary"></b-icon>
            </template>
            <b-dropdown-form>
              <b-form-group label="">
                <b-form-radio v-model="sortPostsBy" size="sm" name="sort-posts-by" value="latest">Latest</b-form-radio>
                <b-form-radio v-model="sortPostsBy" size="sm" name="sort-posts-by" value="likes">Likes</b-form-radio>
                <b-form-radio v-model="sortPostsBy" size="sm" name="sort-posts-by" value="comments">Comments</b-form-radio>
              </b-form-group>
              <b-dropdown-divider></b-dropdown-divider>
              <b-form-group label="">
                <b-form-checkbox v-model="hideLocked" size="sm" name="render-locked" value="true">Hide Locked</b-form-checkbox>
                <b-form-checkbox v-model="hidePromotions" size="sm" name="render-locked" value="true">Hide Promotions</b-form-checkbox>
              </b-form-group>
            </b-dropdown-form>
          </b-dropdown>
        </section>
      </b-col>
    </b-row>

    <section class="row">
      <article
        v-for="(feedItem, index) in renderedItems"
        :key="feedItem.id"
        :class="feedClass"
        v-observe-visibility="index === renderedItems.length - 1 ? endPostVisible : false"
      >
        <div class="tag-debug">INDEX: {{ index }}</div>
        <!-- for now we assume posts; eventually need to convert to a DTO (ie more generic 'feedItem') : GraphQL ? -->
        <PostDisplay
          :post="feedItem"
          :session_user="session_user"
          :use_mid="true"
          @delete-post="deletePost"
        />
      </article>
      <article class="load-more-item" :class="feedClass">
        <b-card :class="{ 'cursor-pointer': !moreLoading && !isLastPage }" @click="onLoadMoreClick">
          <div class="w-100 d-flex my-3 justify-content-center" >
            <fa-icon v-if="moreLoading" icon="spinner" spin size="lg" />
            <span v-else-if="isLastPage">End Of Content</span>
            <span v-else>Load More</span>
          </div>
        </b-card>
      </article>
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

    feedClass() {
      return {
        'col-sm-12': !this.isGridLayout,
        'col-sm-4': this.isGridLayout,
        'mb-3': true,
      }
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
    sortPostsBy: null,
    renderedItems: [], // this will likely only be posts
    renderedPages: [], // track so we don't re-load same page (set of posts) more than 1x
    lastPostVisible: false,
    moreLoading: true,
    limit: 5, // %FIXME: un-hardcode

    hideLocked: false,
    hidePromotions: false,
    isGridLayout: false,

  }),

  mounted() {
    // window.addEventListener('scroll', this.onScroll)
  },
  beforeDestroy() {
    // window.removeEventListener('scroll', this.onScroll)
  },

  created() {
    this.$store.dispatch('getFeeddata', { 
      timelineId: this.timelineId, 
      isHomefeed: this.is_homefeed,
      page: 1, 
      limit: this.limit, 
    })

    eventBus.$on('update-post', postId => {
      console.log('components.timelines.PostFeed - eventBus.$on(update-post)')
      this.updatePost(postId) 
    })

    eventBus.$on('update-feed', () => {
      console.log('components.timelines.PostFeed - eventBus.$on(update-feed)')
      this.reloadFromFirstPage();
    })
  },

  methods: {

    toggleGridLayout() {
      this.isGridLayout = !this.isGridLayout
      eventBus.$emit('set-feed-layout', this.isGridLayout )
        /*
         */
    },

    endPostVisible(isVisible) {
      this.lastPostVisible = isVisible
      if (isVisible && !this.moreLoading && !this.isLastPage) {
        this.loadNextPage()
      }
    },

    onLoadMoreClick() {
      if (!this.moreLoading && !this.isLastPage) {
        this.loadNextPage()
      }
    },

    onScroll(e) {
      const atBottom = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
      if (atBottom && !this.isLoading) {
        this.loadNextPage()
      }
    },

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
    },

    async deletePost(postId) {
      const url = `/posts/${postId}`
      const response = await axios.delete(url)
      //this.$store.dispatch('getFeeddata', { timelineId: this.timelineId, page: 1, limit: this.limit, isHomefeed: this.is_homefeed })
      this.reloadFromFirstPage();
    },

    // additional page loads
    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadNextPage() {
      if ( !this.moreLoading && !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.moreLoading = true;
        this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.$store.dispatch('getFeeddata', { 
          timelineId: this.timelineId, 
          isHomefeed: this.is_homefeed,
          page: this.nextPage, 
          limit: this.limit, 
          sortBy: this.sortPostsBy, 
          hideLocked: this.hideLocked, 
          hidePromotions: this.hidePromotions, 
        })
      }
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset();
      this.$store.dispatch('getFeeddata', { 
        page: 1, 
        timelineId: this.timelineId, 
        isHomefeed: this.is_homefeed,
        limit: this.limit, 
        sortBy: this.sortPostsBy, 
        hideLocked: this.hideLocked, 
        hidePromotions: this.hidePromotions,
      })
    },

    doReset() {
      this.renderedPages = []
      this.renderedItems = []
      this.lastPostVisible = false
      this.moreLoading = true
    },

  },

  watch: {

    hideLocked (newVal) {
      this.$refs.feedCtrls.hide(true)
      this.reloadFromFirstPage();
    },

    hidePromotions (newVal) {
      this.$refs.feedCtrls.hide(true)
      this.reloadFromFirstPage();
    },

    sortPostsBy (newVal) {
      this.$refs.feedCtrls.hide(true)
      this.reloadFromFirstPage();
    },

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
.feed-ctrl {
  background: #fff;
  border: solid #a5a5a5 1px;
  border-radius: 3px;
}
.tag-debug {
  display: none;
  /*
   */
}

</style>
