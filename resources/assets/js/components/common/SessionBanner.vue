<template>
  <div v-if="!is_loading" class="session_banner-crate tag-crate">
    <header class="masthead text-white text-center" v-bind:style="{ backgroundImage: 'url(' + follower.cover.filepath + ')' }">
      <section class="overlay"></section>
      <section class="avatar-img">
        <a :href="`/${follower.username}`">
          <b-img thumbnail rounded="circle" class="w-100 h-100" :src="follower.avatar.filepath" :alt="follower.name" :title="follower.name"></b-img>
        </a>
      </section>
      <section class="dropdown profile-ctrl">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown button
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </section>
    </header>
    <div class="avatar-profile d-flex justify-content-between pt-3 pb-4">
      <section class="avatar-details">
        <h2 class="avatar-name my-0">
          <a :href="`/${follower.username}`">{{ follower.name }}</a>
          <span v-if="follower.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </h2>
        <p class="avatar-mail my-0">
        <a :href="`/${follower.username}`">@{{ follower.username }}</a>
        </p>
      </section>

      <ul class="list-unstyled list-inline tag-stats my-0 mr-5">
        <li class="tag-post_count list-inline-item mr-3"><span>{{ timeline.userstats.post_count }} <b-icon icon="bookmarks-fill" variant="default"></b-icon></span></li>
        <li class="tag-follower_count list-inline-item mr-3"><span>{{ timeline.userstats.follower_count }} <b-icon icon="people-fill" variant="default"></b-icon></span></li>
        <li class="tag-like_count list-inline-item mr-3"><span>{{ timeline.userstats.like_count }} <b-icon icon="heart-fill" variant="default"></b-icon></span></li>
        <li class="tag-following_count list-inline-item"><span>{{ timeline.userstats.following_count }} <b-icon icon="person-check-fill" variant="default"></b-icon></span></li>
      </ul>
    </div>
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
body .tag-crate {
  background-color: #fff;
}

body header.masthead {
  position: relative;
}
body header.masthead {
  padding-top: 12rem;
  padding-bottom: 12rem;
}
body header.masthead {
  position: relative;
  background-color: #343a40;
  background-size: cover;
  padding-top: 8rem;
  padding-bottom: 8rem;
}

body header.masthead .profile-ctrl.dropdown {
  position: absolute;
  top: 0;
  right: 0;
}

body .avatar-img {
  position: absolute;
  left: 16px;
  top: 185px; /* %TODO: bg image height - 1/2*avatar height */
  width: 130px;
  height: 130px;
}

body .avatar-details a {
  /*
  color: #4a5568;
  color: #7F8FA4;
  */
  color: #555;
  text-decoration: none;
  text-transform: capitalize;
}

body .avatar-details {
  margin-left: 200px;
  font-weight: 400;
}
body .avatar-details h2 {
  font-size: 20px;
}
body .avatar-details p {
  font-size: 16px;
}
</style>

