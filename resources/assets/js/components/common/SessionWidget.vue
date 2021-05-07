<template>
  <div v-if="!isLoading" class="session_widget-crate tag-crate">
    <section>

      <b-card
        :img-src="follower.cover.filepath"
        :img-alt="follower.name"
        img-top
        tag="article"
        class="background"
        >
        <div class="avatar-img">
          <a :href="`/${follower.username}`">
            <b-img thumbnail rounded="circle" class="w-100 h-100" :src="follower.avatar.filepath" :alt="follower.name" :title="follower.name"></b-img>
          </a>
        </div>

        <div class="avatar-profile d-flex justify-content-between">
          <div class="avatar-details">
            <h2 class="avatar-name my-0">
              <a :href="`/${follower.username}`">{{ follower.name }}</a>
              <span v-if="follower.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="primary" font-scale="1"></b-icon></span>
            </h2>
            <p class="avatar-mail my-0">
              <a :href="`/${follower.username}`">@{{ follower.username }}</a>
            </p>
          </div>
          <div class="go-live">
            <span><b-icon icon="broadcast-pin" variant="primary" font-scale="2"></b-icon></span>
          </div>
        </div>

        <ul class="activity-list list-group list-group-horizontal justify-content-center mt-3">
          <li class="list-group-item">
            <a :href="`/${follower.username}/posts`">
              <div class="activity-name">Posts</div>
              <div class="activity-count">{{ stats.post_count }}</div>
            </a>
          </li>
          <li class="list-group-item">
            <a :href="`/${follower.username}/followers`">
              <div class="activity-name">Fans</div>
              <div class="activity-count">{{ stats.follower_count }}</div>
            </a>
          </li>
          <li class="list-group-item">
            <a :href="`/${follower.username}/following`">
              <div class="activity-name">Subscribed</div>
              <div class="activity-count">{{ stats.following_count }}</div>
            </a>
          </li>
          <li class="list-group-item">
            <a :href="`/${follower.username}/settings/earnings`">
              <div class="activity-name">Earnings</div>
              <div class="activity-count">${{ (stats.earnings/100).toFixed(2) }}</div>
            </a>
          </li>
        </ul>

      </b-card>

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
    follower() {
      return this.timeline.user;
    },
    stats() {
      return this.timeline.userstats;
    },

    isLoading() {
      return !this.session_user || !this.timeline
    }
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
body .card .card-body {
  padding-top: 5px;
}
body .card.background {
  position: relative;
}
body .card.background .avatar-details {
  margin-left: 58px;
}
body .card.background .avatar-img {
  position: absolute;
  left: 8px;
  top: 90px; /* bg image height - 1/2*avatar height */
  width: 60px;
  height: 60px;
}
body .card.background .avatar-img img {
}
body .card.background img.card-img-top {
  overflow: hidden;
  height: 120px;
}
body .card .avatar-details h2.avatar-name  {
  font-size: 16px;
}
body .card .avatar-details h2.avatar-name > a {
  color: #4a5568;
  text-decoration: none;
  text-transform: capitalize;
}
body .card .avatar-details .avatar-mail  {
  font-size: 14px;
}
body .card .avatar-details .avatar-mail > a {
  color: #7F8FA4;
  text-decoration: none;
}
body .card .activity-list .list-group-item {
  border: none;
  padding: 0 0.5rem;
}
body .card .activity-list .list-group-item a {
  text-decoration: none;
}
body .card .activity-list .list-group-item .activity-name {
  font-size: 12px;
  color: #4a5568;
  text-transform: uppercase;
  text-align: center;
  padding-bottom: 0px;
  font-weight: 600;
}
body .card .activity-list .list-group-item .activity-count {
  color: #7F8FA4;
  text-align: center;
}
</style>

