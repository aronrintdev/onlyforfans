<template>
  <div v-if="!isLoading" class="follow_ctrl-crate tag-crate my-3">
    <b-card tag="article" class="OFF-mb-2">
      <b-card-text>
        <ul class="list-unstyled">
          <li><b-button :disabled="timeline.is_owner" @click="renderMessage" variant="primary" class="w-100">Message</b-button></li>
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
              <span>Follow</span>
            </b-button>
          </li>
          <li>
            <b-button @click="renderTip" :disabled="timeline.is_owner" variant="primary" class="w-100 mt-3">
              <span>Send Tip</span>
            </b-button>
          </li>
        </ul>
        <div :class="{ 'normal-view': !isFullVisiable }">
          <VueMarkdown :html="false" :source="timeline.about || ''" />
        </div>
        <div v-if="!isFullVisiable && timeline.about && timeline.about.length > showChars" class="toggle-read-more text-primary text-right mr-3 mt-1" @click="isFullVisiable = !isFullVisiable">Read more</div>
        <ul class="list-unstyled">
          <li v-if="timeline.userstats.website">Website: <a :href="timeline.userstats.website" class="tag-website" target="_blank">{{ timeline.userstats.website }}</a></li>
          <li v-if="timeline.userstats.instagram">Instagram: <a :href="timeline.userstats.instagram" class="tag-instagram" target="_blank">{{ timeline.userstats.instagram }}</a></li>
        </ul>
        <ul class="list-unstyled list-details">
          <li v-if="timeline.userstats.city"><span><fa-icon icon="map-pin" class="map-pin-icon" /> {{ timeline.userstats.city }}</span></li>
          <li v-if="timeline.userstats.country"><span><fa-icon icon="globe" /> {{ timeline.userstats.country }}</span></li>
        </ul>
      </b-card-text>
      <router-link v-if="timeline.is_owner" :to="{ name: 'settings.profile', params: { } }" class="float-right mr-3 cursor-pointer">Edit</router-link>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/eventBus'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

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
    isFullVisiable: false,
    showChars: 95,
  }),

  created() { },

  methods: { 
    renderFollow() {
      this.$log.debug('FollowCtrl.renderFollow() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: this.timeline,
        }
      })
    },
    renderSubscribe() {
      this.$log.debug('FollowCtrl.renderSubscribe() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: this.timeline,
        }
      })
    },
    renderTip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: this.timeline,
          resource_type: 'timelines', 
        },
      })
    },
    renderMessage() {
      this.$router.push({
        name: 'chatthreads.dashboard',
      })
    },
  },

  components: {
    VueMarkdown,
  },
}
</script>

<style scoped>

body #modal-send_tip .modal-body {
  padding: 0;
}

.normal-view {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

li a.tag-website {
  margin-left: 20px;
}

li a.tag-instagram {
  margin-left: 5px;
}


.toggle-read-more {
  cursor: pointer;
}

.map-pin-icon {
  margin-left: 3px;
  margin-right: 4px;
}

</style>

