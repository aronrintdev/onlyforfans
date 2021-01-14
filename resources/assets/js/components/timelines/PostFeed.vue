<template>
  <div class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div v-infinite-scroll="loadMore" infinite-scroll-disabled="is_loading" infinite-scroll-distance="limit">
        <article v-for="(fi, idx) in rendereditems" :key="fi.id" class="col-sm-12">
          <!-- for now we assume posts; eventually need to convert to a DTO -->
          <!-- img-src="https://picsum.photos/600/300/?image=25" -->
          <b-card tag="article" class="OFF-mb-2">
            <b-card-img v-if="fi.mediafiles.length>0" :src="fi.mediafiles[0].filepath" alt="Image"></b-card-img>
            <b-card-text>
              {{ fi.description }}
            </b-card-text>
            <b-button href="#" variant="primary">Go somewhere</b-button>
          </b-card>

        </article>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '../../app';
import { VueInfiniteScroll } from 'vue-infinite-scroll';

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
  },
}
</script>

<style scoped>
</style>

