<template>
  <div v-if="!is_loading" class="session_banner-crate tag-crate">
    <header class="masthead text-white text-center" v-bind:style="{ backgroundImage: 'url(' + follower.cover.filepath + ')' }">
      <div class="overlay"></div>
      <div class="dropdown profile-ctrl">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown button
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </div>
      <!--
        <div class="container">
        <div class="row">
        <div class="col-xl-9 mx-auto">
        <h1 class="mb-5">Build a landing page for your business or project and generate more leads!</h1>
        </div>
        <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
        <form>
        <div class="form-row">
        <div class="col-12 col-md-9 mb-2 mb-md-0">
        <input type="email" class="form-control form-control-lg" placeholder="Enter your email...">
        </div>
        <div class="col-12 col-md-3">
        <button type="submit" class="btn btn-block btn-lg btn-primary">Sign up!</button>
        </div>
        </div>
        </form>
        </div>
        </div>
        </div>
      -->
    </header>
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
header.masthead {
  position: relative;
}
header.masthead {
  padding-top: 12rem;
  padding-bottom: 12rem;
}
header.masthead {
  position: relative;
  background-color: #343a40;
  /* background: url(../img/bg-masthead.jpg) no-repeat center center; */
  background-size: cover;
  padding-top: 8rem;
  padding-bottom: 8rem;
}

header.masthead .profile-ctrl.dropdown {
  position: absolute;
  top: 0;
  right: 0;
}
</style>

