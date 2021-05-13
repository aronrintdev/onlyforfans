<template>
  <div v-if="!isLoading">

    <b-card title="Favorites">
      <hr />
      <b-tabs card>

        <b-tab title="Favorites" active>
          <b-card-text>
            <b-table hover 
              id="favorites-table"
              :items="favorites.data"
              :fields="favoriteFields"
              :current-page="currentPage"
            >
              <template #cell(favoriteable_id)="row">
                <router-link :to="{ name: 'posts.show', params: { slug: row.value } }">Details</router-link>
              </template>
            </b-table>
            <b-pagination
              v-model="currentPage"
              :total-rows="totalRows"
              :per-page="perPage"
              aria-controls="favorites-table"
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
      'favorites',
    ]),

    totalRows() {
      return this.favorites.meta ? this.favorites.meta.total : 1
    },

    isLoading() {
      return !this.session_user || !this.user_settings || !this.favorites
    },
  },

  watch: { },

  data: () => ({
    perPage: 10,
    currentPage: 1,

    favoriteFields: [
      {
        key: 'created_at',
        label: 'Date',
        formatter: (value, key, item) => {
          return moment(value).format('MMMM Do, YYYY')
        }
      },
      {
        key: 'favoriteable_type',
        label: 'Type',
      },
      {
        key: 'creator.name',
        label: 'Creator',
      },
      {
        key: 'favoriteable_id',
        label: '',
      },
    ],
  }),

  created() {
    this.getFavorites({ 
      //seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
  },

  methods: {
    ...Vuex.mapActions({
      getFavorites: "getFavorites",
    }),

    pageClickHandler(e, page) {
      this.getFavorites({ 
        //seller_id: this.session_user.id,
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
