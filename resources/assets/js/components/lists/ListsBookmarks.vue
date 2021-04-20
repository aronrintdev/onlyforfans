<template>
  <div v-if="!isLoading">

    <b-card title="Bookmarks">
      <b-card-text>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
//import Vuex from 'vuex';

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({
    moment: moment,
    following: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

  }),

  methods: {
    getPagedData(type=null) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }
      if (this.filter && this.filter!=='none') {
        params.type = this.filterToType // PostTipped, etc
      }
      axios.get( route('notifications.index'), { params } ).then( response => {
        this.notifications = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },
  },

  mounted() {
  },

  created() {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

