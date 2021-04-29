<template>
  <div v-if="!isLoading" class="feed-crate tag-posts tag-crate" :class="{ 'tag-grid-layout': isGridLayout }">
    <div class="tag-debug">
      <ul>
        <li>Timeline ID: {{ timeline.id | niceGuid }}</li>
        <li>Price: {{ timeline.price }}</li>
        <li>Slug: {{ timeline.slug }}</li>
      </ul>
    </div>

    <b-row>
      <b-col>
        <section class="feed-ctrl my-3 px-2 py-2 d-flex flex-column OFF-text-center flex-md-row justify-content-center justify-content-md-between">
          <b-nav v-if="!is_homefeed" pills>
            <b-nav-item @click="setFeedType('default')" :active="feedType==='default'">All</b-nav-item>
            <b-nav-item @click="setFeedType('photos')" :active="feedType==='photos'">Photos</b-nav-item>
            <b-nav-item @click="setFeedType('videos')" :active="feedType==='videos'">Videos</b-nav-item>
          </b-nav>
          <article class="d-none d-md-block">
            <div @click="renderTip" style="" class="btn">
              <div style="font-size: 1.2rem; margin-top: 0.1rem" class="text-primary tag-ctrl">$</div>
            </div>
            <div style="margin-top: 0.3rem" class="btn">
              <b-icon icon="chat" font-scale="1.5" variant="primary" class="tag-ctrl" /> 
            </div>
            <div @click="renderFollow" style="margin-top: 0.3rem" class="btn">
              <b-icon :icon="timeline.is_following ? 'eye-fill' : 'eye'" font-scale="1.5" variant="primary" class="tag-ctrl" /> 
            </div>
            <div @click="toggleGridLayout" style="margin-top: 0.3rem" class="btn">
              <b-icon icon="grid" scale="1.2" variant="primary"></b-icon>
            </div>
            <div @click="toggleFavorite" style="margin-top: 0.3rem" class="btn">
                <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
                <fa-icon v-else fixed-width :icon="['far', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
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
          </article>
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
        <ImageDisplay v-if="feedType==='photos'"
          :mediafile="feedItem"
          :session_user="session_user"
          :use_mid="true"
        />
        <PostDisplay v-else
          :post="feedItem"
          :session_user="session_user"
          :use_mid="true"
          @delete-post="deletePost"
        />
      </article>
      <article class="load-more-item col-sm-12">
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
import ImageDisplay from '@components/timelines/elements/ImageDisplay'

export default {
  components: {
    PostDisplay,
    ImageDisplay,
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
    timelineSlug() {
      return this.timeline.slug
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
    sortPostsBy: null, // %TODO: rename to sortBy
    renderedItems: [], // this will likely only be posts
    renderedPages: [], // track so we don't re-load same page (set of posts) more than 1x
    lastPostVisible: false,
    moreLoading: true,
    limit: 5, // %FIXME: un-hardcode

    hideLocked: false,
    hidePromotions: false,
    isGridLayout: false,
    feedType: 'default',

    isFavoritedByMe: false, // is timeline/feed a favorite

  }),

  mounted() {
    // window.addEventListener('scroll', this.onScroll)
  },
  beforeDestroy() {
    // window.removeEventListener('scroll', this.onScroll)
  },

  created() {
    this.$store.dispatch('getFeeddata', { 
      feedType: this.feedType,
      timelineId: this.timelineId,  // only valid if not home feed
      isHomefeed: this.is_homefeed,
      page: 1, 
      limit: this.limit, 
    })

    eventBus.$on('update-posts', postId => {
      console.log('components.timelines.PostFeed - eventBus.$on(update-posts)')
      this.updatePost(postId) 
    })

    eventBus.$on('update-timelines', (timelineId) => {
      this.$log.debug('components.timelines.PostFeed - eventBus.$on(update-timelines)')
      this.reloadFromFirstPage();
    })

    //eventBus.$on('update-feed', () => {
    //console.log('components.timelines.PostFeed - eventBus.$on(update-feed)')
    //this.reloadFromFirstPage();
    //})
  },

  methods: {

    setFeedType(feedType) {
      if (this.isHomefeed) {
        return // skip
      }

      // only for specific timelines (non-home feed)
      this.feedType = feedType
      switch (feedType) {
        case 'photos':
        case 'videos':
          this.isGridLayout = true
          break
        default:
          this.isGridLayout = false
      }
      eventBus.$emit('set-feed-layout', this.isGridLayout )
      this.reloadFromFirstPage()
    },

    renderTip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: this.timeline,
          resource_type: 'timelines', 
        },
      })
    },

    renderFollow() {
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: this.timeline,
        }
      })
    },
    renderSubscribe() {
      eventBus.$emit('open-modal', {
        key: 'render-subscribe', 
        data: {
          timeline: this.timeline,
        }
      })
    },

    async toggleFavorite() {
      let response
      if (this.isFavoritedByMe) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline.id,
        })
        this.isFavoritedByMe = false
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline.id,
        })
        this.isFavoritedByMe = true
      }
    },

    toggleGridLayout() {
      this.isGridLayout = !this.isGridLayout
      eventBus.$emit('set-feed-layout', this.isGridLayout )
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
      this.reloadFromFirstPage()
    },

    // additional page loads
    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadNextPage() {
      if ( !this.moreLoading && !this.isLoading && (this.nextPage <= this.lastPage) ) {
        this.moreLoading = true;
        this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.$store.dispatch('getFeeddata', { 
        feedType: this.feedType,
          timelineId: this.timelineId,  // only valid if not home feed
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
      this.doReset()
      this.$store.dispatch('getFeeddata', { 
        feedType: this.feedType,
        page: 1, 
        timelineId: this.timelineId,  // only valid if not home feed
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

    timelineId() {
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
