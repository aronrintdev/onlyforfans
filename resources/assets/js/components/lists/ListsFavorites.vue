<template>
  <div v-if="!isLoading">

    <b-card>
      <b-row>
        <b-col>
          <h2 class="card-title mt-1 mb-3"><span class="tag-title">Favorites</span></h2>
        </b-col>
      </b-row>
      <b-card-text>
        <div>
          <b-tabs content-class="mt-3">
            <!--
            <b-tab title="All" active>
              <TabFavoritesAll :session_user="session_user" />
            </b-tab>
            -->
            <b-tab title="Posts" active>
              <TabFavoritesPosts :session_user="session_user" />
            </b-tab>
            <b-tab title="Creators">
              <TabFavoritesCreators :session_user="session_user" />
            </b-tab>
            <b-tab title="Photos">
              <TabFavoritesPhotos :session_user="session_user" />
            </b-tab>
            <!-- <b-tab title="Videos">
              <TabFavoritesVideos :session_user="session_user" />
            </b-tab> -->
          </b-tabs>
        </div>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import { eventBus } from '@/eventBus'
//import TabFavoritesAll from '@components/lists/favorites/TabAll'
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
  }),

  methods: { },

  mounted() { },

  created() { },

  components: {
    //TabFavoritesAll,
    TabFavoritesPosts,
    TabFavoritesCreators,
    TabFavoritesPhotos,
    TabFavoritesVideos,
  },
}
</script>

<style lang="scss" scoped>
</style>

