<template>
  <div v-if="!isLoading" class="suggested_feed-crate tag-crate">
    <ul class="list-suggested list-group">
      <li class="tag-heading list-group-item my-3"><h3 class="my-0">Suggested People</h3></li>
      <li v-for="(t, i) in rendered" :key="t.id || i" class="list-group-item my-3">
        <MiniProfile :timeline="t" />
      </li>
    </ul>
    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="suggested-timelines"
      v-on:page-click="pageClickHandler"
      class="mt-3"
    ></b-pagination>
  </div>
</template>

<script>
import Vuex from 'vuex';
import MiniProfile from '@components/user/MiniProfile'

export default {
  components: {
    MiniProfile,
  },
  props: {
  },

  computed: { 
    isLoading() {
      return !this.rendered 
    },

    totalRows() {
      return this.timelines.length
    },
  },

  data: () => ({
    timelines: null, // full dataset
    rendered: null, // paged (rendered) data subset

    perPage: 5,
    currentPage: 1,
  }),

  mounted() {
  },

  created() {
    this.getDataset()
  },

  methods: {
    // %NOTE: Pagination is done here in client, as we download a superset of randomly selected
    // suggested timelines from the server
    getDataset(type=null) {
      /*
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }
      if (this.filter && this.filter!=='none') {
        params.type = this.filterToType // PostTipped, etc
      }
       */
      axios.get( route('timelines.suggested') ).then( response => {
        this.timelines = response.data.data || [];
        this.setRendered()
      });
    },

    setRendered() {
      const begin = ((this.currentPage - 1) * this.perPage)
      const end = begin + this.perPage
      this.rendered = this.timelines.slice(begin, end)
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.setRendered()
      //this.getPagedData()
    },
  },

}
</script>

<style lang="scss" scoped>
ul.list-suggested {
  background-color: #fff;
  padding: 0.5rem 0.5rem;
  li.tag-heading {
    h3 {
      font-size: 18px;
      text-transform: capitalize;
      color: #4a5568;
      font-weight: 500;
    }
  }
}


.list-group-item {
  border: none;
  padding: 0 0.5rem;
  a {
    text-decoration: none;
  }
}
</style>
