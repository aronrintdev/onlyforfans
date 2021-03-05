<template>
  <div v-if="!is_loading" class="crate tag-crate row mb-3 mx-0">
    <section class="d-flex flex-wrap justify-content-between w-100">
      <div @click="createStory()" class="story">
        <router-link :to="{ name: 'stories.dashboard' }">
          <b-icon icon="plus-circle" variant="primary" font-scale="2" />
        </router-link>
      </div>
      <div v-for="story in stories" :key="story.id" class="mb-3 story">
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
    ...Vuex.mapState(['is_loading']),
  },

  data: () => ({
    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
  }),

  created() {
    //this.$store.dispatch('getMe');
    this.$store.dispatch('getStories', {
      filters: {
        user_id: this.session_user.id,
      },
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
    /*
    session_user (newVal, oldVal) {
      if ( newVal.id && (newVal.id > 0) && !this.isLoadedHack ) {
        this.$store.dispatch('getStories', { 
          filters: {
            user_id: newVal.id,
          }
        });
        this.isLoadedHack = true;
      }
    },
    */
  },

  components: {},
}
</script>

<style lang="scss" scoped>
$size: 40px;
$margin: 16px;
.crate {
  max-height: $size * 2 + $margin + 2px;
  overflow-y: auto;

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
