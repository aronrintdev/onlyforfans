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
          <b-tabs content-class="mt-3" v-model="tabIndex">
            <!--
            <b-tab title="All" active>
              <TabFavoritesAll :session_user="session_user" />
            </b-tab>
            -->
            <b-tab active>
              <template #title>
                Posts {{ tabIndex === 0 ? `(${favoritePosts})` : '' }}
              </template>
              <TabFavoritesPosts :session_user="session_user" :setTabInfo="setTabInfo" />
            </b-tab>
            <b-tab>
              <template #title>
                Creators {{ tabIndex === 1 ? `(${favoriteCreators})` : '' }}
              </template>
              <TabFavoritesCreators :session_user="session_user" :setTabInfo="setTabInfo" />
            </b-tab>
            <b-tab>
              <template #title>
                Photos {{ tabIndex === 2 ? `(${favoritePhotos})` : '' }}
              </template>
              <TabFavoritesPhotos :session_user="session_user" :setTabInfo="setTabInfo" />
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
    tabIndex: 0,
    favoritePosts: 0,
    favoriteCreators: 0,
    favoritePhotos: 0,
    currentTab: 'photos',
  }),

  methods: {
    setTabInfo(tab, count) {
      if (tab === 'posts') this.favoritePosts = count
      else if (tab === 'creators') this.favoriteCreators = count
      else this.favoritePhotos = count
    },
  },

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

