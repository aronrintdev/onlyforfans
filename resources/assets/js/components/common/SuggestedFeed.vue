<template>
  <div v-if="!!timelines" class="suggested_feed-crate tag-crate">

    <ul class="list-group">

      <li v-for="(t,idx) in timelines" :key="t.id" class="list-group-item">

        <b-card
          :img-src="t.user.cover.filepath"
          :img-alt="t.user.name"
          img-top
          tag="article"
          class="background"
          >
          <div class="avatar-img">
            <a :href="`/${t.user.username}`">
              <b-img thumbnail rounded="circle" class="w-100 h-100" :src="t.user.avatar.filepath" :alt="t.user.name" :title="t.user.name"></b-img>
            </a>
          </div>

          <div class="avatar-profile d-flex justify-content-between">
            <div class="avatar-details">
              <h2 class="avatar-name my-0">
                <a :href="`/${t.user.username}`">{{ t.user.name }}</a>
                <span v-if="t.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
              </h2>
              <p class="avatar-mail my-0">
              <a :href="`/${t.user.username}`">@{{ t.user.username }}</a>
              </p>
            </div>
          </div>

        </b-card>

      </li>

    </ul>							

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {

  props: {
  },

  computed: {
    //...Vuex.mapState(['is_loading']),
  },

  data: () => ({
    timelines: null,
  }),

  created() {
    const payload = {};
    axios.get(`/timelines-suggested`).then( response => {
      const json = response.data;
      this.timelines = json.timelines || [];
    });
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
</style>
