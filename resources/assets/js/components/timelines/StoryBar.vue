<template>
  <div  v-if="!is_loading" class="story_bar-crate tag-crate row mb-3 mx-0">

    <section class="d-flex">
      <div @click="createStory()"><a :href="`/${username}/stories/create`"><b-icon icon="plus-circle" variant="primary" font-scale="2"></b-icon></a></div>
      <div v-for="(s, idx) in stories" :key="s.id" class="ml-3">
        <a :href="`/${username}/stories/player`"
           class="box-story">
          <b-img v-if='s.stype==="image"' thumbnail fluid rounded="circle" class="p-0" :src="s.mediafiles[0].filepath" alt="Story Thumbnail"></b-img>
          <span v-else class="tag-colorfill" :style="`background-color: ${bgColor(s)}`">&nbsp;</span>
        </a>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    ...Vuex.mapState(['stories']),
    ...Vuex.mapState(['is_loading']),

    username() { // story owner
      return this.timeline.username;
    },
  },

  data: () => ({
    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
  }),

  created() {
    //this.$store.dispatch('getMe');
    this.$store.dispatch('getStories', { 
      filters: {
        user_id: this.timeline.user.id,
      }
    });
  },

  methods: {

    createStory() {
    },

    bgColor(story) {
      return Object.keys(story).includes('background-color') 
        ? story.cattrs['background-color']
        : 'yellow';
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

  components: {
  },
}
</script>

<style scoped>
body .story_bar-crate .b-icon {
  height: 40px;
}
body .story_bar-crate .box-story img {
  width: 40px;
  height: 40px;
}
body .story_bar-crate .box-story .tag-colorfill {
  width: 40px;
  height: 40px;
  display: block;
  border-radius: 50%;
}
</style>
