<template>
  <b-card no-body class="background mb-5"> 
    <!-- widget: Timeline -->
    <b-card-img :src="coverImage" alt="timeline.slug" top></b-card-img>

    <b-card-body class="py-1">

      <div class="last-seen">Last seen {{ moment(timeline.updated_at).format('MMM D') }}</div>

      <div class="banner-ctrl ">
        <b-dropdown no-caret right ref="bannerCtrls" variant="transparent" id="banner-ctrl-dropdown" class="tag-ctrl"> 
          <template #button-content>
            <fa-icon fixed-width icon="ellipsis-v" style="font-size:1.2rem; color:#fff" />
          </template>
          <b-dropdown-item v-clipboard="getTimelineUrl(timeline)">Copy link to profile</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item disabled @click="doReport(timeline)">Report</b-dropdown-item>
        </b-dropdown>
      </div>
      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
          <b-img-lazy thumbnail rounded="circle" class="w-100 h-100" :src="avatarImage" :alt="timeline.slug" :title="timeline.name" />
        </router-link>
        <OnlineStatus :user="{ id: timeline.user_id }" size="lg" :textInvisible="false" />
      </div>

      <div class="shareable-id">
        <b-card-title class="mb-1 subscriber-card">
          <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">{{ timeline.name }}</router-link>
          <span v-if="access_level==='premium'" class="subscriber">
            <b-badge variant='info'>
              Subscriber
            </b-badge>
          </span>
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">@{{ timeline.slug }}</router-link>
        </b-card-sub-title>
        <OnlineStatus :user="{ id: timeline.user_id }" :indicatorVisible="false" />
      </div>

      <slot></slot>

      <div class="mt-2 mb-2">
        <small v-if="access_level==='premium'" class="text-muted">Subscribed since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
        <small v-else class="text-muted">Following for free since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
      </div>

    </b-card-body>

  </b-card>
</template>

<script>
import { eventBus } from '@/eventBus'
//import { DateTime } from 'luxon'
import moment from 'moment'
import OnlineStatus from '@components/common/OnlineStatus'

export default {

  props: {
    timeline: null,
    access_level: null,
    created_at: null,
  },

  computed: {
    isLoading() {
      return !this.timeline || !this.access_level || !this.created_at
    },

    coverImage() {
      const { cover } = this.timeline
      return cover ? cover.filepath : '/images/locked_post.png'
    },

    avatarImage() {
      const { avatar } = this.timeline
      return avatar ? avatar.filepath : '/images/default_avatar.png'
    },
  },

  data: () => ({
    moment: moment,
  }),

  methods: {

    // shareable in this context is the [shareables] record
    // shareable.shareable in this context is related shareable record (eg timeline)
    doReport(timeline) {
      const userToBlock = timeline.user
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(timeline) {
      return route('spa.index', timeline.slug)
    },
  },

  mounted() { },
  created() { },
  components: {
    OnlineStatus,
  },

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
.subscriber {
  margin-left: 15px;
  font-size: 18px;
}
.subscriber-card {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}
</style>

