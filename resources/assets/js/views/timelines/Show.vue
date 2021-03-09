<template>
  <div>
    <div class="container" id="view-show_timeline" v-if="!isLoading">

      <section class="row">
        <article class="col-sm-12">
          <Banner :session_user="session_user" :timeline="timeline" :follower="timeline.user" />
        </article>
      </section>

      <section class="row">

        <aside class="col-md-5 col-lg-4">
          <FollowCtrl :session_user="session_user" :timeline="timeline" />
        </aside>

        <main class="col-md-7 col-lg-8">
          <PostFeed :session_user="session_user" :timeline="timeline" :is_homefeed="false" />
        </main>

      </section>

    </div>

    <b-modal id="modal-send_tip" size="sm" title="Send a Tip" hide-footer body-class="p-0" v-if="!isLoading">
      <SendTip :session_user="session_user" :timeline="timeline" />
    </b-modal>

    <b-modal id="modal-follow" title="Follow" ok-only v-if="!isLoading">
      <div class="w-100 d-flex justify-content-center">
        <div v-if="timeline.is_following">
          <b-button @click="doFollow" type="submit" variant="warning">
            <span v-if="timeline.is_subscribed">Click to Unsubscribe</span>
            <span v-else>Click to Unfollow</span>
          </b-button>
        </div>
        <div v-else>
          <b-button @click="doFollow" type="submit" variant="primary"><span>Click to Follow</span></b-button>
          <b-button @click="doSubscribe" type="submit" variant="success"><span>Click to Subscribe</span></b-button>
        </div>
      </div>
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
    slug: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.slug || !this.timeline
    }
  },

  data: () => ({
    timeline: null,
  }),

  mounted() {
    if (this.slug) {
      this.load()
    }
    if (!this.session_user) {
    }
  },

  methods: {
    async load() {
      try { 
        const response = await this.axios.get(this.$apiRoute('timelines.show', { timeline: this.slug }))
        this.timeline = response.data.data
      } catch (error) {
        this.$log.error(error)
      }
    },

    async doFollow() {
      const response = await this.axios.put( route('timelines.follow', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow');
      this.load()
    },

    async doSubscribe() {
      const response = await this.axios.put( route('timelines.subscribe', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow');
      this.load()
    },
  },

  watch: {
    slug(value) {
      this.load()
    }
  },
}
</script>

<style scoped>
</style>
