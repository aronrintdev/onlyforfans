<template>
  <div class="container" id="view-explore">
    <section class="row">
      <main class="col-12">
        <PublicPostFeed :mediafiles="mediafiles" @loadMore="loadMore" />
        <div class="text-center" v-if="loading">
          <b-spinner class="loading"></b-spinner>
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

<style lang="scss" scoped>
.loading {
  width: 2.5em;
  height: 2.5em;
  margin: 5em;
}
@media (max-width: 767px) {
  #view-explore .col-12 {
    padding: 5px;
  }
  .loading {
    width: 2em;
    height: 2em;
    margin: 4em;
  }
}
</style>
