<template>
  <div v-if="!isLoading" class="follow_ctrl-crate tag-crate">
    <b-card tag="article" class="OFF-mb-2">
      <b-card-text>
        <ul class="list-unstyled">
          <li><b-button :disabled="timeline.is_owner" variant="primary" class="w-100">Message</b-button></li>
          <li v-if="timeline.is_following">
            <b-button v-if="timeline.is_subscribed" @click="renderSubscribe" :disabled="timeline.is_owner" variant="warning" class="w-100 mt-3">
              <span>Unsubscribe</span>
            </b-button>
            <b-button v-else @click="renderFollow" :disabled="timeline.is_owner" variant="warning" class="w-100 mt-3">
              <span>Unfollow</span>
            </b-button>
          </li>
          <li v-else >
            <b-button @click="renderFollow" :disabled="timeline.is_owner" variant="primary" class="w-100 mt-3">
              <span>Follow For Free</span>
            </b-button>
          </li>
          <li>
            <b-button @click="renderTip" :disabled="timeline.is_owner" variant="primary" class="w-100 mt-3">
              <span>$ Send Tip</span>
            </b-button>
          </li>
        </ul>
        <p>{{ timeline.about }}</p>
        <ul class="list-unstyled">
          <li>Website: <a :href="timeline.userstats.website" class="tag-website">{{ timeline.userstats.website }}</a></li>
          <li>Instagram: <a :href="timeline.userstats.instagram" class="tag-instagram">{{ timeline.userstats.instagram }}</a></li>
        </ul>
        <ul class="list-unstyled list-details">
          <li><span><b-icon icon="geo-fill" variant="default"></b-icon> {{ timeline.userstats.city }}</span></li>
          <li><span><b-icon icon="globe" variant="default"></b-icon> {{ timeline.userstats.country }}</span></li>
        </ul>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/app'

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.timeline
    }
  },

  data: () => ({
  }),

  created() { },

  methods: { 
    renderFollow() {
      console.log('FollowCtrl.renderFollow() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-follow', 
        data: {
          timeline_id: this.timeline.id,
        }
      })
    },
    renderSubscribe() {
      console.log('FollowCtrl.renderSubscribe() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-subscribe', 
        data: {
          timeline_id: this.timeline.id,
        }
      })
    },
    renderTip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip', 
        data: { resource_type: 'timelines', resource_id: this.timeline.id },
      })
    },
  },

  components: { },
}
</script>

<style scoped>

body #modal-send_tip .modal-body {
  padding: 0;
}

</style>

