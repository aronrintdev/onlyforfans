<template>
  <div v-if="!isLoading" class="suggested_feed-crate tag-crate">

    <h3 class="my-0">Suggested People</h3>

    <b-pagination
      class="pagenav-top mt-3"
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="suggested-timelines"
      v-on:page-click="pageClickHandler"
      hide-goto-end-buttons
      hide-ellipsis
      limit="0"
    >
      <template v-slot:page>
        <fa-icon fixed-width icon="circle" />
      </template>
    </b-pagination>
    <ul class="list-suggested list-group">
      <li v-for="(t, i) in rendered" :key="t.id || i" class="list-group-item my-3">
        <MiniProfile :timeline="t" />
      </li>
    </ul>
    <b-pagination
      class="pagenav-bottom mt-0"
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="suggested-timelines"
      v-on:page-click="pageClickHandler"
      align="center"
      hide-goto-end-buttons
    >
      <template #page="{ page, active }">
        <fa-icon :icon="active ? ['fas', 'circle'] : ['far', 'circle']" />
      </template>
    </b-pagination>
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

    perPage: 3,
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

<style lang="scss" >
body .suggested_feed-crate {

  background-color: #fff;
  padding: 0.5rem 0.5rem;

  ul.list-suggested {
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

  // hide active page so we only show navigation at top
  ul.pagenav-top li.page-item.active {
    display: none;
  }
  
  ul.pagenav-bottom {
    // hide navigation at bottom
    li.page-item:first-child, li.page-item:last-child {
      display: none;
    }
    // format bottom page status
    li.page-item .page-link {
      margin: 0;
      padding: 0 0.2rem;
      border: none;
    }
    li.page-item button {
      background-color: #fff;
    }
    li.page-item button > svg {
      color: #c5c5c5;
      font-size: 0.5rem;
    }
    li.page-item.active button > svg {
      color: #555555;
    }

  }
}

</style>
