<template>
  <div class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div v-infinite-scroll="loadMore" infinite-scroll-disabled="is_loading" infinite-scroll-distance="limit">
        <article v-for="(fi, idx) in rendereditems" :key="fi.id" class="col-sm-12 mb-3">
          <!-- for now we assume posts; eventually need to convert to a DTO -->
          <!-- img-src="https://picsum.photos/600/300/?image=25" -->

          <b-card header-tag="header" tag="article" class="superbox-post OFF-mb-2">
            <template #header>
                <div class="post-author">
                  <section class="user-avatar">
                    <a :href="'/'+fi.timeline.username"><b-img :src="fi.user.avatar.filepath" :alt="fi.timeline.name" :title="fi.timeline.name"></b-img></a>
                  </section>
                  <section class="user-post-details">
                    <ul class="list-unstyled">
                      <li>
                        <a href="'/'+fi.timeline.username" title="" data-toggle="tooltip" data-placement="top" class="username">{{ fi.timeline.name }}</a>
                        <span v-if="fi.timeline.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
                        <div class="small-text"></div>
                      </li>
                      <li>
                        <timeago :datetime="fi.created_at" :auto-update="60"></timeago>
                        <span v-if="fi.location" class="post-place"> at <a target="_blank" :href="'/get-location/'+fi.location">
                            <b-icon icon="geo-fill" variant="primary" font-scale="1"></b-icon> {{ fi.location }}</a>
                        </span>
                      </li>
                    </ul>
                  </section>
                </div>
            </template>
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
  },
}
</script>

<style scoped>
.superbox-post .post-author .user-avatar {
    width: 40px;
    height: 40px;
    float: left;
    margin-right: 10px;
}
.superbox-post .post-author .user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
}
.superbox-post .post-author .user-post-details ul {
    padding-left: 50px;
    margin-bottom: 0;
}
.superbox-post .post-author .user-post-details ul > li {
    color: #859AB5;
    font-size: 16px;
    font-weight: 400;
}
.superbox-post .post-author .user-post-details ul > li .username {
    text-transform: capitalize;
}

.superbox-post .post-author .user-post-details ul > li .post-time {
    color: #4a5568;
    font-size: 12px;
    letter-spacing: 0px;
    margin-right: 3px;
}
.superbox-post .post-author .user-post-details ul > li:last-child {
    font-size: 14px;
}
.superbox-post .post-author .user-post-details ul > li {
    color: #859AB5;
    font-size: 16px;
    font-weight: 400;
}
/*
.small-text {
    font-size: 13px !important;
}
*/
</style>
