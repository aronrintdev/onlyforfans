<template>
  <!-- %FIXME: duplciate of components/common/SessionWidget? -->
  <div v-if="!isLoading" class="mini_my_stats_widget-crate tag-crate">
    <section>
      <b-card
        :img-src="session_user.cover.filepath"
        :img-alt="timeline.name"
        img-top
        tag="article"
        class="background"
      >
        <div class="avatar-img">
          <a :href="`/${timeline.slug}`">
            <b-img-lazy
              thumbnail
              rounded="circle"
              class="w-100 h-100"
              :src="session_user.avatar.filepath"
              :alt="timeline.name"
              :title="timeline.name"
            ></b-img-lazy>
            <OnlineStatus :user="session_user" size="lg" :textInvisible="false" />
          </a>
        </div>

        <div class="avatar-profile d-flex justify-content-between">
          <div class="avatar-details">
            <h2 class="avatar-name my-0">
              <a :href="`/${timeline.slug}`">{{ timeline.name }}</a>
              <span v-if="timeline.verified" class="verified-badge">
                <fa-icon icon="check-circle" class="text-primary" />
              </span>
            </h2>
            <p class="avatar-mail my-0">
              <a :href="`/${timeline.slug}`">@{{ timeline.slug }}</a>
            </p>
          </div>
          <!-- <div class="go-live">
            <span>
              <fa-icon icon="podcast" class="text-primary" size="2x" />
            </span>
          </div> -->
        </div>

        <ul class="activity-list list-group list-group-horizontal justify-content-center mt-3">
          <li class="list-group-item">
            <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
              <div class="activity-name">Posts</div>
              <div class="activity-count">{{ stats.post_count }}</div>
            </router-link>
          </li>
          <li class="list-group-item">
            <router-link :to="{ name: 'lists.followers', params: { slug: timeline.slug } }">
              <div class="activity-name">Fans</div>
              <div class="activity-count">{{ stats.follower_count }}</div>
            </router-link>
          </li>
          <li class="list-group-item">
            <router-link :to="{ name: 'lists.following', params: { slug: timeline.slug } }">
              <div class="activity-name">Subscribed</div>
              <div class="activity-count">{{ stats.following_count }}</div>
            </router-link>
          </li>
          <li class="list-group-item">
            <router-link :to="{ name: 'statements.dashboard', params: { slug: timeline.slug } }">
              <div class="activity-name">Earnings</div>
              <div class="activity-count">${{ (stats.earnings / 100).toFixed(2) }}</div>
            </router-link>
          </li>
        </ul>
      </b-card>
    </section>
  </div>
</template>

<script>
import Vuex from 'vuex'
import OnlineStatus from '@components/common/OnlineStatus'

export default {
  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.timeline;
    },

    stats() {
      return this.timeline.userstats
    },
  },

  data: () => ({}),

  created() {},

  methods: {},

  components: {
    OnlineStatus,
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
  margin-left: 80px;
}
body .card.background .avatar-img {
  position: absolute;
  left: 8px;
  top: 75px; /* bg image height - 1/2*avatar height */
  width: 80px;
  height: 80px;
}
body .card.background .avatar-img .onlineStatus {
  position: absolute;
  top: 50px;
  right: 0;
}
body .card.background .avatar-img img {
}
body .card.background img.card-img-top {
  object-fit: cover;
  overflow: hidden;
  height: 120px;
}
body .card .avatar-details h2.avatar-name {
  font-size: 16px;
}
body .card .avatar-details h2.avatar-name > a {
  color: #4a5568;
  text-decoration: none;
  text-transform: capitalize;
}
body .card .avatar-details .avatar-mail {
  font-size: 14px;
}
body .card .avatar-details .avatar-mail > a {
  color: #7f8fa4;
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
  font-size: 15px;
  color: #4a5568;
  text-transform: uppercase;
  text-align: center;
  padding-bottom: 0px;
  font-weight: 600;
}
body .card .activity-list .list-group-item .activity-count {
  color: #7f8fa4;
  text-align: center;
}
</style>
