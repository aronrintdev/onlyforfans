<template>
  <div v-if="!isLoading" class="follow_ctrl-crate tag-crate">
    <b-card tag="article" class="OFF-mb-2">
      <b-card-text>
        <ul class="list-unstyled">
          <li><b-button :disabled="timeline.is_owner" variant="primary" class="w-100">Message</b-button></li>
          <li v-if="timeline.is_following">
            <b-button :disabled="timeline.is_owner" v-b-modal.modal-follow variant="warning" class="w-100 mt-3">
              <span v-if="timeline.is_subscribed">Unsubscribe</span>
              <span v-else>Unfollow</span>
            </b-button>
          </li>
          <li v-else >
            <b-button :disabled="timeline.is_owner" v-b-modal.modal-follow variant="primary" class="w-100 mt-3">
              <span>Follow For Free</span>
            </b-button>
          </li>
          <li>
            <b-button :disabled="timeline.is_owner" v-b-modal.modal-send_tip variant="primary" class="w-100 mt-3">
              <span>$ Send Tip</span>
              </b-button>
          </li>
        </ul>
        <p>{{ timeline.about }}</p>
        <ul class="list-unstyled">
          <li><a :href="timeline.userstats.website" class="tag-website">Website</a></li>
          <li><a :href="timeline.userstats.instagram" class="tag-instagram">Instagram</a></li>
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

  methods: { },

  components: { },
}
</script>

<style scoped>

body #modal-send_tip .modal-body {
  padding: 0;
}

</style>

