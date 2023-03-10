<template>
  <div v-if="!isLoading" class="feed-crate tag-posts tag-crate" :class="isGridLayout ? 'tag-grid-layout': 'tag-linear-layout'">
    <div class="tag-debug">
      <ul>
        <li>Timeline ID: {{ timeline.id | niceGuid }}</li>
        <li>Price: {{ timeline.price }}</li>
        <li>Slug: {{ timeline.slug }}</li>
      </ul>
    </div>

    <b-row>
      <b-col>
        <section v-if="!is_schedulefeed" class="mt-3 px-2 py-2 d-flex flex-column flex-md-row justify-content-center justify-content-md-between" :class="is_homefeed ? 'feed-ctrl-home' : 'feed-ctrl'">
          <b-nav v-if="!is_homefeed" pills>
            <b-nav-item @click="setFeedType('default')" :active="feedType==='default'">All</b-nav-item>
            <b-nav-item @click="setFeedType('photos')" :active="feedType==='photos'">Photos ({{ totalPhotosCount }})</b-nav-item>
            <b-nav-item @click="setFeedType('videos')" :active="feedType==='videos'">Videos ({{ totalVideosCount }})</b-nav-item>
          </b-nav>
          <article v-else>
            <!-- empty placeholder to preserve justify arrangment in flex area -->
          </article>
          <article class="d-md-block ml-auto mr-0">
            <!-- <div v-if="!is_homefeed" @click="renderTip" class="btn">
              <fa-icon icon="dollar-sign" class="tag-ctrl text-primary" />
            </div>
            <div v-if="!is_homefeed" class="btn">
              <fa-icon :icon="['far', 'comment']" class="tag-ctrl text-primary" />
            </div>
            <div v-if="!is_homefeed" @click="renderFollow" class="btn">
              <fa-icon :icon="timeline.is_following ? 'eye' : ['far', 'eye']" class="tag-ctrl text-primary" /> 
            </div>
            <div v-if="!is_homefeed" @click="toggleFavorite" class="btn">
                <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable text-primary" />
                <fa-icon v-else fixed-width :icon="['far', 'star']" class="clickable text-primary" />
            </div> -->
            <div v-if="!mobile" @click="toggleGridLayout" class="btn">
              <fa-icon :icon="['far', 'grip-horizontal']" class="text-primary" />
            </div>
            <b-dropdown no-caret ref="feedCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
              <template #button-content>
                <fa-icon :icon="['far', 'filter']" class="text-primary" style="font-size: 1.2rem;" />
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

    <section class="row feed-list">
      <article
        v-for="(feedItem, index) in listItems"
        :key="feedItem.id + index"
        :class="feedClass"
        class="feed-list-item"
        v-observe-visibility="index === listItems.length - 1 ? endPostVisible : false"
      >
        <div class="tag-debug">INDEX: {{ index }}</div>
        <ImageDisplay v-if="feedType==='photos' || feedType==='videos'"
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
      <article class="load-more-item col-sm-12 my-3">
        <b-card :class="{ 'cursor-pointer': !moreLoading && !isLastPage }" class="load-more-item-content" @click="onLoadMoreClick">
          <div class="w-100 d-flex my-3 text-secondary justify-content-center" >
            <fa-icon v-if="moreLoading" icon="spinner" spin size="lg" />
            <span v-else-if="isLastPage">End of content</span>
            <span v-else>Load More</span>
          </div>
        </b-card>
      </article>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'

export default {
  components: {
    PostDisplay,
    ImageDisplay,
  },

  props: {
    is_homefeed: null,
    is_schedulefeed: null,
    session_user: null,
    timeline: null,
    currentFeedType: null,
    setCurrentFeedType: { type: Function },
  },

  computed: {
    ...Vuex.mapState(['feeddata']), // should include keys: data (posts) and meta (pagination info), and links 
    ...Vuex.mapState(['unshifted_timeline_post']),
    ...Vuex.mapState([ 'mobile' ]),

    isLoading() {
      return !this.feeddata || !this.session_user || !this.timeline
    },

    feedClass() {
      return {
        'col-sm-12': !this.isGridLayout,
        'col-sm-4': this.isGridLayout,
        'mt-4': true,
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
    listItems() {
      if (this.is_schedulefeed) {
        return this.renderedItems.filter(it => Boolean(it.schedule_datetime));
      }
      return this.renderedItems.filter(it => !it.schedule_datetime);
    }
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
    totalPhotosCount: 0,
    totalVideosCount: 0,
  }),

  mounted() {
    this.isFavoritedByMe = this.timeline.is_favorited;
    // window.addEventListener('scroll', this.onScroll)
  },
  beforeDestroy() {
    // window.removeEventListener('scroll', this.onScroll)
    eventBus.$off('update-timelines');
  },

  created() {
    this.feedType = this.is_schedulefeed ? 'schedule' : this.feedType;

    // Get photos & videos feed total count
    this.getMediaCount()

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
      //this.$log.debug('components.timelines.PostFeed - eventBus.$on(update-timelines)')
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
      this.setCurrentFeedType(feedType)
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
      const idx = this.renderedItems.findIndex( ri => ri.id === postId );
      this.$store.dispatch('getQueueMetadata');
      const response = await axios.get( route('posts.show', postId) );
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
        //this.$log.debug('loadNextPage', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
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
      this.$store.dispatch('getQueueMetadata')
      this.getMediaCount()
    },

    doReset() {
      this.renderedPages = []
      this.renderedItems = []
      this.lastPostVisible = false
      this.moreLoading = true
    },

    getMediaCount() {
      axios.get(route('timelines.getPhotosVideosCount', this.timelineId))
        .then((response) => {
          this.totalPhotosCount = response.data.photos || 0;
          this.totalVideosCount = response.data.videos || 0;
        })
    }
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

    // newVal is a post object
    unshifted_timeline_post (newVal, oldVal) {
      console.log('PostFeed - watch:unshifted_timeline_post', { newVal, oldVal })

      if ( (this.feedType !== 'schedule') && newVal.schedule_datetime ) {
        return // do nothing (??)
      }

      // update total media counts
      newVal.mediafiles.forEach(mf => {
        if (mf.is_image) {
          this.totalPhotosCount += 1
        } else if (mf.is_video) {
          this.totalVideosCount += 1
        }
      })

      switch (this.feedType) {
        case 'photos':
          newVal.mediafiles.forEach(mf => {
            if (mf.is_image) {
              this.renderedItems.unshift(mf);
            }
          })
          break
        case 'videos':
          newVal.mediafiles.forEach(mf => {
            if (mf.is_video) {
              this.renderedItems.unshift(mf);
            }
          })
          break
        case 'schedule':
          if (newVal.schedule_datetime) {
            if (!this.isLastPage && this.renderedItems.length >= 5) {
              this.renderedItems.pop();
            }
            this.renderedItems.unshift(newVal);
          }
          break
        default: // feedType === 'default'
           this.renderedItems.unshift(newVal);
      }
    },

    feeddata (newVal, oldVal) {
      if ( !this.renderedPages.includes(newVal.meta.current_page) ) {
        this.renderedPages.push(newVal.meta.current_page)
        this.renderedItems = this.renderedItems.concat(newVal.data) // the actual posts
        this.moreLoading = false
      } else {
        const items = [...this.renderedItems]
        newVal.data.forEach(p => {
          const idx = items.findIndex(it => it.id === p.id)
          items[idx] = p
        })
        this.renderedItems = items
      }
    },

    currentFeedType(newVal) {
      if (newVal === 'photos') this.setFeedType('photos')
    }
  },
}
</script>

<style lang="scss" scoped>
.feed-ctrl {
  background: #fff;
  border: solid #dfdfdf 1px;
  border-radius: 3px;
  .btn {
    font-size: 1.2rem;
  }
}

.tag-debug {
  display: none;
  /*
   */
}

.load-more-item {
  &-content {
    background-color: transparent;
    border: none;
  }
}

#view-home_timeline .feed-crate .feed-ctrl-home {
  margin-top: 0 !important;
}

// Remove top margin on post display only in linear feed view on home timeline
#view-home_timeline .feed-crate.tag-linear-layout .feed-list .feed-list-item:first-child {
  margin-top: 0 !important;
}

</style>
