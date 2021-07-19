<template>
  <div class="container" id="view-explore">
    <section class="row">
      <main class="col-12">
        <PublicPostFeed :mediafiles="mediafiles" @loadMore="loadMore" />
        <div class="text-center py-5 m-5" v-if="loading">
          <b-spinner style="width: 3rem; height: 3rem;"></b-spinner>
        </div>
      </main>
    </section>
  </div>
</template>

<script>
import PublicPostFeed from '@components/timelines/PublicPostFeed.vue';

export default {
  components: {
    PublicPostFeed,
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
    async fetchFeed(page) {
      this.loading = true;
      const response = await axios.get(route('timelines.publicfeed'), {
        params: {
          page,
        }
      });
      const posts = response.data.data.filter(dt => dt.mediafiles.length > 0);
      const mediafiles = posts.map(post => ({
        ...post.mediafiles[0],
        mediaCount: post.mediafiles.length,
      }));
      this.mediafiles = this.mediafiles.concat(mediafiles);
      this.loading = false;
    },
    loadMore() {
      this.page += 1;
      this.fetchFeed(this.page);
    }
  }
}
</script>
