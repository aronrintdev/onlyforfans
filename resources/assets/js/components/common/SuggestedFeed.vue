<template>
  <div v-if="!isLoading" class="suggested_feed-crate tag-crate px-3">

    <section class="ctrl-top d-flex justify-content-between align-items-center">
      <h5 class="my-0 flex-grow-1">SUGGESTIONS</h5>
      <span @click="getDataset" class="tag-clickable sync">
        <fa-icon icon="sync" class="text-primary" />
      </span>
      <span @click="toggleFree" class="tag-clickable filter" :class="filters.paidOnly ? 'text-success' : 'text-primary'">
        <fa-icon icon="dollar-sign" />
      </span>
      <b-pagination
        class="pagenav-top my-0"
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="suggested-timelines"
        v-on:page-click="pageClickHandler"
        hide-goto-end-buttons
        hide-ellipsis
      >
      </b-pagination>
    </section>
    

    <ul class="list-suggested list-group">
      <li v-for="(t, i) in rendered" :key="t.id || i" class="list-group-item">
        <MiniProfile :timeline="t" class="mb-3" />
      </li>
    </ul>
    <b-pagination
      class="pagenav-bottom py-3 my-0"
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="suggested-timelines"
      v-on:page-click="pageClickHandler"
      align="center"
      hide-goto-end-buttons
    >
      <template #page="{ page, active }"><fa-icon :icon="active ? ['fas', 'circle'] : ['far', 'circle']" /></template>
    </b-pagination>
  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/eventBus'
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

    filters: {
      paidOnly: false,
    },

    perPage: 5,
    currentPage: 1,
  }),

  mounted() {
  },

  created() {
    this.getDataset()

    eventBus.$on('update-timelines', (timelineId) => {
      const timeline = this.timelines.find(t => t.id === timelineId)
      if (timeline) {
        this.getDataset()
      }
    })

    eventBus.$on('set-feed-layout',  isGridLayout  => {
      this.isGridLayout = isGridLayout
    })
  },

  methods: {
    toggleFree() {
      this.filters.paidOnly = !this.filters.paidOnly
      this.getDataset()
    },

    // %NOTE: Pagination is done here in client, as we download a superset of randomly selected
    // suggested timelines from the server
    getDataset(type=null) {
      const params = {}
      params.paid_only = this.filters.paidOnly
      axios.get( route('timelines.suggested'), { params }).then( response => {
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
  border: solid #dfdfdf 1px;
  border-radius: 0.25rem;
  //padding: 0.5rem 0.5rem;

  .ctrl-top > * {
    padding: 0.8rem 0;
    margin: 0 0.5rem;
  }
  .tag-clickable {
    cursor: pointer;
  }

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

  .list-group-item:last-child > .mb-3 {
    margin-bottom: 0 !important;
  }

  // hide active page so we only show navigation at top
  ul.pagenav-top {
    // Manually hide the numbered pages in nav, only show left (prev) and right (next) arrows
    li.page-item {
      display: none;
    }
    li.page-item:first-child,
    li.page-item:last-child {
      display: list-item;
    }
    li.page-item .page-link {
      font-size: 1.5rem;
      border: none;
    }
    li.page-item .page-link:focus,
    li.page-item .page-link:visited,
    li.page-item .page-link:hover {
      box-shadow: none;
      color: inherit;
      background-color: inherit;
    }
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
