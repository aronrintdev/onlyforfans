<template>
  <b-card no-body class="background mb-5"> 
    <!-- widget: User -->
    <b-card-img :src="user.cover.filepath" alt="user.username" top></b-card-img>

    <b-card-body class="py-1">

      <div class="banner-ctrl ">
        <b-dropdown no-caret right ref="bannerCtrls" variant="transparent" id="banner-ctrl-dropdown" class="tag-ctrl"> 
          <template #button-content>
            <fa-icon fixed-width icon="ellipsis-v" style="font-size:1.2rem; color:#fff" />
          </template>
          <b-dropdown-item v-clipboard="getTimelineUrl(user)">Copy link to profile</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item @click="doRestrict(user)">Restrict</b-dropdown-item>
          <b-dropdown-item @click="doBlock(user)">Block</b-dropdown-item>
          <b-dropdown-item @click="doReport(user)">Report</b-dropdown-item>
        </b-dropdown>
      </div>

      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug } }">
          <b-img thumbnail rounded="circle" class="w-100 h-100" :src="user.avatar.filepath" :alt="user.username" :title="user.name" />
        </router-link>
      </div>


      <div class="sharee-id">
        <b-card-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">{{ user.name }}</router-link>
          <fa-icon v-if="access_level==='premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">@{{ user.username }}</router-link>
        </b-card-sub-title>
      </div>

      <b-card-text class="mb-2"><fa-icon fixed-width icon="star" style="color:#007bff" /> Add to favorites</b-card-text>

      <b-button variant="outline-primary">Message</b-button>
      <b-button variant="outline-danger">Restrict</b-button>
      <b-button variant="outline-warning">Discount</b-button>
      <div>
        <small v-if="access_level==='premium'" class="text-muted">subscribed since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
        <small v-else class="text-muted">following for free since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
      </div>

    </b-card-body>

  </b-card>
</template>

<script>
import { eventBus } from '@/app'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    user: null, // eg: the follower, or favorited user,
    slug: null,
    access_level: null,
    created_at: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user || !this.access_level || !this.created_at
    },
  },

  data: () => ({
    moment: moment,
  }),

  methods: {
    // shareable in this context is the [shareables] record
    // shareable.sharee in this context is related user record
    doBlock(shareable) {
      console.log('doBlock() TODO'); // %TODO
    },

    doRestrict(shareable) {
      console.log('doRestrict() TODO'); // %TODO
    },

    doReport(shareable) {
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(user) {
      return route('spa.index', user.username)
    }
  },

  mounted() { },
  created() { },
  components: { },

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
</style>

