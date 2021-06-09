<template>
  <div v-if="!isLoading" class="crate tag-crate crate-story_bar row OFF-mb-3 mx-0">
    <section class="d-flex flex-wrap justify-content-start w-100">
      <div @click="createStory()" class="story">
        <router-link :to="{ name: 'stories.dashboard' }">
          <fa-icon class="mt-1" :icon="['far', 'plus-circle']" size="2x" />
        </router-link>
      </div>
      <div v-for="story in stories" :key="story.id" class="ml-3 mb-3 story">
        <router-link :to="{ name: 'stories.player' }" class="box-story">
          <b-img
            v-if="story.stype === 'image'"
            rounded="circle"
            class="p-0"
            :src="story.mediafiles[0].filepath"
            alt="Story Thumbnail"
          />
          <span v-else class="tag-colorfill" :style="`background-color: ${bgColor(story)}`">&nbsp;
          </span>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script>
import Vuex from 'vuex'

export default {
  props: {
    session_user: null,
  },

  computed: {
    ...Vuex.mapState(['stories']),

    isLoading() {
      return !this.stories
    },
  },

  data: () => ({
    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
  }),

  created() {
    this.$store.dispatch('getStories', {
      //user_id: this.session_user.id,
      following: 1,
      stypes: 'image', // %FIXME: should be 'photo' (ideally we use PHP ENUM?)
    })
  },

  methods: {
    createStory() {},

    bgColor(story) {
      return Object.keys(story).includes('background-color')
        ? story.customAttributes['background-color']
        : 'yellow'
    },
  },

  watch: {
  },

  components: {},
}
</script>

<style lang="scss" scoped>
$size: 40px;
$margin: 16px;
.crate {
  .story {
    margin-left: $margin / 2;
    margin-right: $margin / 2;
    margin-bottom: $margin;
  }

  .b-icon {
    height: $size;
  }

  .box-story img {
    width: $size;
    height: $size;
  }

  .box-story .tag-colorfill {
    width: $size;
    height: $size;
    display: block;
    border-radius: 50%;
  }
}
</style>
