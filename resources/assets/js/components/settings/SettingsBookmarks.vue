<template>
  <div v-if="!isLoading">

    <b-card title="Bookmarks">
      <hr />
      <b-tabs card>

        <b-tab title="Bookmarks" active>
          <b-card-text>
            <b-table hover 
              id="bookmarks-table"
              :items="bookmarks.data"
              :fields="bookmarkFields"
              :current-page="currentPage"
            >
              <template #cell(bookmarkable_id)="row">
                <router-link :to="{ name: 'posts.show', params: { slug: row.value } }">Details</router-link>
              </template>
            </b-table>
            <b-pagination
              v-model="currentPage"
              :total-rows="totalRows"
              :per-page="perPage"
              aria-controls="bookmarks-table"
              v-on:page-click="pageClickHandler"
            ></b-pagination>
          </b-card-text>
        </b-tab>

      </b-tabs>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    ...Vuex.mapState([
      'bookmarks',
    ]),

    totalRows() {
      return this.bookmarks.meta ? this.bookmarks.meta.total : 1
    },

    isLoading() {
      return !this.session_user || !this.user_settings || !this.bookmarks
    },
  },

  watch: { },

  data: () => ({
    perPage: 10,
    currentPage: 1,

    bookmarkFields: [
      {
        key: 'created_at',
        label: 'Date',
        formatter: (value, key, item) => {
          return moment(value).format('MMMM Do, YYYY')
        }
      },
      {
        key: 'bookmarkable_type',
        label: 'Type',
      },
      {
        key: 'creator.name',
        label: 'Creator',
      },
      {
        key: 'bookmarkable_id',
        label: '',
      },
    ],
  }),

  created() {
    this.getBookmarks({ 
      seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
  },

  methods: {
    ...Vuex.mapActions({
      getBookmarks: "getBookmarks",
    }),

    pageClickHandler(e, page) {
      console.log('pageClickHandler', page)
      this.getBookmarks({ 
        seller_id: this.session_user.id,
        page: page,
        take: this.perPage,
      })
    },

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>
