<template>
  <div>

    <div class="container" id="view-show_timeline" v-if="state === 'loaded'">

      <section class="row">
        <article class="col-sm-12">
          <Banner :session_user="session_user" :timeline="timeline" />
        </article>
      </section>

      <section class="row">

        <aside class="col-md-5 col-lg-4">
          <FollowCtrl :session_user="session_user" :timeline="timeline" />
        </aside>

        <main class="col-md-7 col-lg-8">
          <PostFeed :session_user="session_user" :timeline="timeline" />
        </main>

      </section>

    </div>

    <b-modal id="modal-send_tip" size="sm" title="Send a Tip" hide-footer body-class="p-0" v-if="state === 'loaded'">
      <SendTip :session_user="session_user" :timeline="timeline" />
    </b-modal>

    <b-modal id="modal-follow" title="Follow" ok-only v-if="state === 'loaded'">
      <p class="my-4">Follow Modal</p>
    </b-modal>

  </div>
</template>

<script>
import Vuex from 'vuex';
import PostFeed from '@components/timelines/PostFeed.vue';
import StoryBar from '@components/timelines/StoryBar.vue';
import Banner from '@components/timelines/Banner.vue';
import FollowCtrl from '@components/common/FollowCtrl.vue';
import SendTip from '@components/modals/SendTip.vue';

export default {
  components: {
    PostFeed,
    StoryBar,
    Banner,
    FollowCtrl,
    SendTip,
  },

  props: {
    username: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),
  },

  data: () => ({
    state: 'loading', // loading | loaded
    timeline: null,
  }),

  mounted() {
    if (this.username) {
      this.load()
    }
    if (!this.session_user) {
      this.getMe()
    }
  },

  methods: {
    ...Vuex.mapActions([ 'getMe' ]),
    load() {
      this.state = 'loading'
      this.axios.get(this.$apiRoute('timelines.show', { timeline: this.username }))
      .then(response => {
        this.timeline = response.data.timeline
        this.state = 'loaded'
      })
      .catch(error => {
        this.$log.error(error)
      })
    }
  },

  watch: {
    username(value) {
      this.load()
    }
  },
}
</script>

<style scoped>
</style>
