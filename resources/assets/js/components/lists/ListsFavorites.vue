<template>
  <div v-if="!isLoading">

    <b-card title="Favorites">
      <b-card-text>
        <div>
          <b-tabs content-class="mt-3">
            <b-tab title="All" active>
              <!-- table -->
              <TabFavoritesAll>
              </TabFavoritesAll>
            </b-tab>
            <b-tab title="Posts">
              <!-- layout like followers -->
              <TabFavoritesPosts>
              </TabFavoritesPosts>
            </b-tab>
            <b-tab title="Creators">
              <TabFavoritesCreators>
              </TabFavoritesCreators>
            </b-tab>
            <b-tab title="Photos">
              <TabFavoritesPhotos>
              </TabFavoritesPhotos>
            </b-tab>
            <b-tab title="Videos">
              <TabFavoritesVideos>
              </TabFavoritesVideos>
            </b-tab>
          </b-tabs>
        </div>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import { eventBus } from '@/app'
import moment from 'moment'
import TabFavoritesAll from '@components/lists/favorites/TabAll'
import TabFavoritesPosts from '@components/lists/favorites/TabPosts'
import TabFavoritesCreators from '@components/lists/favorites/TabCreators'
import TabFavoritesPhotos from '@components/lists/favorites/TabPhotos'
import TabFavoritesVideos from '@components/lists/favorites/TabVideos'

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
    TabFavoritesAll,
    TabFavoritesPosts,
    TabFavoritesCreators,
    TabFavoritesPhotos,
    TabFavoritesVideos,
  },
}
</script>

<style lang="scss" scoped>
</style>

