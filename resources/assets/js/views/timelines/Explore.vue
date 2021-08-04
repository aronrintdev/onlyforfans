<template>
  <div class="container" id="view-explore">
    <section class="row">
      <main class="col-12">
        <PublicPostFeed :mediafiles="publicPosts" @loadMore="loadMore" :loading="loading" />
      </main>
    </section>
  </div>
</template>

<script>
import Vuex from 'vuex'
import PublicPostFeed from '@components/timelines/PublicPostFeed.vue';

export default {
  components: {
    PublicPostFeed,
  },

  computed: {
    ...Vuex.mapState('posts', [ 'publicPosts' ]),
  },

  data: () => ({
    page: 1,
    mediafiles: [],
    loading: false,
  }),

  mounted() {
    // Fetch public photos & videos
    this.fetchFeed(1);
  },
  methods: {
    ...Vuex.mapMutations('posts', [ 'SET_PUBLIC_POSTS' ]),
    async fetchFeed(page) {
      this.loading = true;
      const response = await axios.get(route('timelines.publicfeed'), {
        params: {
          page,
        }
      });
      const posts = response.data.data.filter(dt => dt.mediafile_count > 0);
      const mediafiles = posts.map(post => ({
        ...post.mediafiles.filter(file => file.is_image || file.is_video)[0],
        mediaCount: post.mediafile_count,
        post,
      }));
      this.mediafiles = this.mediafiles.concat(mediafiles);
      this.SET_PUBLIC_POSTS(this.mediafiles);
      this.loading = false;
    },
    loadMore() {
      this.page += 1;
      this.fetchFeed(this.page);
    }
  }
}
</script>

<style lang="scss" scoped>
.loading {
  width: 1.5em;
  height: 1.5em;
  margin: 2.5em;
}
@media (max-width: 767px) {
  #view-explore .col-12 {
    padding: 5px;
  }
}
</style>
