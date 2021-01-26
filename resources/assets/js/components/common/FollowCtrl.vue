<template>
  <div v-if="!is_loading" class="follow_ctrl-crate tag-crate">
    <b-card tag="article" class="OFF-mb-2">
      <b-card-text>
        <ul class="list-unstyled">
          <li class=""><button class="btn btn-primary w-100">Message</button></li>
          <li class=""><button class="btn btn-primary w-100 mt-3">Follow For Free</button></li>
          <li class=""><button class="btn btn-primary w-100 mt-3">$ Send Tip</button></li>
        </ul>
        <p>{{ timeline.about }}</p>
        <ul class="list-unstyled">
          <li><a :href="timeline.user.website" class="tag-website">Website</a></li>
          <li><a :href="timeline.user.instagram" class="tag-instagram">Instagram</a></li>
        </ul>
        <ul class="list-unstyled list-details">
          <li><span><b-icon icon="geo-fill" variant="default"></b-icon> {{ timeline.user.city }}</span></li>
          <li><span><b-icon icon="globe" variant="default"></b-icon> {{ timeline.user.country }}</span></li>
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
    ...Vuex.mapState(['is_loading']),

    follower() {
      return this.timeline.user;
    },
  },

  data: () => ({
  }),

  created() {
  },

  methods: {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

