<template>
  <div>
    <Player v-if="!loading" :username="username" :stories="stories" />
  </div>
</template>

<script>
/**
 * Stories Player View
 */
import Player from '@components/stories/Player'
import Vuex from 'vuex'

export default {
  name: 'StoriesPlayer',
  components: {
    Player,
  },

  props: {
    slug: { type: String, default: '' },
  },

  data: () => ({
    // stories: [],
    loading: true,
  }),

  computed: {
    ...Vuex.mapState(['stories', 'session_user']),
    username() {
      return (this.session_user) ? this.session_user.username : ''
    }
  },

  methods: {
    load() {
      // this.$log.debug('Loading Story', { slug: this.slug })
      // this.loading = true
      // this.axios.get(this.$apiRoute('stories.show', { story: this.slug }))
      //   .then(response => {
      //     this.$log.debug('Finished Loading Story', { response })
      //     this.stories = [ response.data.story ]
      //     this.loading = false
      //   })
      this.loading = true
      if (!this.session_user) {
        setTimeout(this.load, 50)
        return
      }
      this.$store.dispatch('getStories', {
        filters: {
          user_id: this.session_user.id,
        },
      })
    },
  },

  watch: {
    stories(value) {
      if (value && value.length > 0) {
        this.loading = false
      }
    }
  },

  mounted() {
    if (!this.stories || this.stories.length === 0) {
      this.load()
    } else {
      this.loading = false
    }
  },

}
</script>

<style lang="scss" scoped>

</style>