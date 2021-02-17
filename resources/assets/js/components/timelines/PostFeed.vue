<template>
  <div v-if="!is_loading" class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div>
        <article v-for="(fi, idx) in rendereditems" :key="fi.id" class="col-sm-12 mb-3">
          <!-- for now we assume posts; eventually need to convert to a DTO (ie more generic 'feedItem') : GraphQL ? -->
          <ShowPost
              :post=fi
              :session_user=session_user
              v-on:delete-post="deletePost"/>
        </article>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '@/app';
import ShowPost from './ShowPost.vue';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    ...Vuex.mapState(['feeditems']),
    ...Vuex.mapState(['unshifted_timeline_post']),
    ...Vuex.mapState(['is_loading']),

    username() { // feed owner
      return this.timeline.username;
    },
    timelineId() {
      return this.timeline.id;
    },

    currentPage() {
      return this.feeditems.current_page;
    },
    nextPage() {
      return this.feeditems.current_page + 1;
    },
    lastPage() {
      return this.feeditems.last_page;
    },
    isLastPage() {
      return this.feeditems.current_page === this.feeditems.last_page;
    },
  },

  data: () => ({
    rendereditems: [],
    renderedpages: [], // track so we don't re-load same page (set of posts) more than 1x
    limit: 5,
  }),

  mounted() {
    window.addEventListener('scroll', this.onScroll);
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.onScroll);
  },

  created() {
    this.$store.dispatch('getFeeditems', { timelineId: this.timelineId, page: 1, limit: this.limit });
  },

  methods: {

    onScroll(e) {
      const atBottom = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
      if (atBottom && !this.is_loading) {
        this.loadMore();
      }
    },

    async deletePost(postId) {
      const url = `/posts/${postId}`;
      const response = await axios.delete(url);
      this.renderedpages = [];
      this.rendereditems = [];
      this.$store.dispatch('getFeeditems', { timelineId: this.timelineId, page: 1, limit: this.limit });
    },

    // see: https://peachscript.github.io/vue-infinite-loading/guide/#installation
    loadMore() {
      if ( !this.is_loading && (this.nextPage <= this.lastPage) ) {
        console.log('loadMore', { current: this.currentPage, last: this.lastPage, next: this.nextPage });
        this.$store.dispatch('getFeeditems', { timelineId: this.timelineId, page: this.nextPage, limit: this.limit });
      }
    },

  },

  watch: {
    unshifted_timeline_post (newVal, oldVal) {
      console.log('PostFeed - watch:unshifted_timeline_post', { newVal, oldVal });
      this.rendereditems.pop(); // pop the 'oldest' to keep pagination offset correct
      this.rendereditems.unshift(newVal);
    },

    feeditems (newVal, oldVal) {
      if ( !this.renderedpages.includes(newVal.current_page) ) {
        this.renderedpages.push(newVal.current_page);
        this.rendereditems = this.rendereditems.concat(newVal.data);
      }
    },
  },

  components: {
    ShowPost,
  },
}
</script>

<style scoped>
</style>
