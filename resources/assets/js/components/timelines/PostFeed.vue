<template>
  <div class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div v-infinite-scroll="loadMore" infinite-scroll-disabled="is_loading" infinite-scroll-distance="limit">
        <article v-for="(fi, idx) in rendereditems" :key="fi.id" class="col-sm-12 mb-3">
          <!-- for now we assume posts; eventually need to convert to a DTO (ie more generic 'feeditem') : GraphQL ? -->
          <ShowPost :post=fi />
        </article>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '../../app';
import { VueInfiniteScroll } from 'vue-infinite-scroll';
import ShowPost from './ShowPost.vue';

export default {

  props: {
    /*
    vault_pkid: {
      required: true,
      type: Number,
    },
     */
  },

  computed: {
    ...Vuex.mapState(['feeditems']),
    ...Vuex.mapState(['timeline']),
    ...Vuex.mapState(['is_loading']),

    feeddataitems() {
      return this.feeditems.data;
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
    limit: 10,
  }),

  mounted() {
  },

  created() {
    this.$store.dispatch('getFeeditems', { timelineSlug: 'home', page: 1, limit: this.limit });
  },

  methods: {

    loadMore() {
      if ( !this.is_loading && (this.nextPage <= this.lastPage) ) {
        this.$store.dispatch('getFeeditems', { timelineSlug: 'home', page: this.nextPage, limit: this.limit });
      }
    },

  },

  watch: {
    feeditems (newVal, oldVal) {
      if ( !this.renderedpages.includes(newVal.current_page) ) {
        this.renderedpages.push(newVal.current_page);
        this.rendereditems = this.rendereditems.concat(newVal.data);
      }
    },
  },

  components: {
    VueInfiniteScroll,
    ShowPost,
  },
}
</script>

<style scoped>
/*
.small-text {
    font-size: 13px !important;
}
*/
</style>
