<template>
  <div v-if="!is_loading" class="session_banner-crate tag-crate">
    <header
      class="masthead text-white text-center"
      v-bind:style="{ backgroundImage: 'url(' + follower.cover.filepath + ')' }"
    >
      <div class="overlay" />
      <section class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug: follower.username } }">
          <b-img
            thumbnail
            rounded="circle"
            class="w-100 h-100"
            :src="follower.avatar.filepath"
            :alt="follower.name"
            :title="follower.name"
          />
        </router-link>
      </section>
      <section class="dropdown profile-ctrl">
        <b-dropdown id="dropdown-1" text="..." class="OFF-m-md-2">
          <b-dropdown-item>First Action</b-dropdown-item>
          <b-dropdown-item>Second Action</b-dropdown-item>
          <b-dropdown-item>Third Action</b-dropdown-item>
        </b-dropdown>
      </section>
    </header>

    <b-container fluid>
      <b-row class="avatar-profile pt-3 pb-4">
        <b-col cols="12" md="4" offset-md="2" class="avatar-details text-right text-md-left">
          <h2 class="avatar-name my-0">
            <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
              {{ follower.name }}
            </router-link>
            <span v-if="follower.verified" class="verified-badge">
              <b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon>
            </span>
          </h2>
          <p class="avatar-mail my-0">
            <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
              @{{ follower.username }}
            </router-link>
          </p>
          <div>
            <OnlineStatus :user="timeline.user" />
          </div>
        </b-col>

        <b-col cols="12" md="4" offset-md="2" class="tag-stats my-0">
          <Stats :stats="timeline.userstats" />
        </b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
import Vuex from 'vuex'
import Stats from './banner/Stats'
import OnlineStatus from '@components/user/OnlineStatus'

export default {
  components: {
    OnlineStatus,
    Stats,
  },
  props: {
    timeline: null,
  },

  computed: {
    ...Vuex.mapState(['is_loading']),
    ...Vuex.mapGetters(['session_user']),

    follower() {
      return this.timeline.user
    },
  },

  data: () => ({}),

  methods: {},

  created() {},

}
</script>

<style lang="scss" scoped>
.tag-crate {
  background-color: #fff;
}

header.masthead {
  position: relative;
  padding-top: 12rem;
  padding-bottom: 12rem;
  position: relative;
  background-color: #343a40;
  background-size: cover;
  padding-top: 8rem;
  padding-bottom: 8rem;

  .profile-ctrl.dropdown {
    position: absolute;
    top: 0;
    right: 0;
  }
}

/* Why doesn't this CSS have any effect ? */
header.masthead .profile-ctrl.dropdown button {
  color: red !important;
  border: none;
  background: transparent;
}

.avatar-img {
  position: absolute;
  left: 16px;
  top: 185px; /* %TODO: bg image height - 1/2*avatar height */
  width: 130px;
  height: 130px;
}

.avatar-details {
  /*
  margin-left: 172px;
   */
  font-weight: 400;

  a {
    /*
    color: #4a5568;
    color: #7F8FA4;
    */
    color: #555;
    text-decoration: none;
    text-transform: capitalize;
  }

  h2 {
    font-size: 20px;
  }

  p {
    font-size: 16px;
  }
}
</style>
