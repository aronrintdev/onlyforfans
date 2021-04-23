<template>
  <b-card no-body class="background mb-5"> 
    <!-- widget: Timeline -->
    <b-card-img :src="timeline.cover.filepath" alt="timeline.slug" top></b-card-img>

    <b-card-body class="py-1">

      <div class="last-seen">Last seen TBD</div>

      <div class="banner-ctrl ">
        <b-dropdown no-caret right ref="bannerCtrls" variant="transparent" id="banner-ctrl-dropdown" class="tag-ctrl"> 
          <template #button-content>
            <fa-icon fixed-width icon="ellipsis-v" style="font-size:1.2rem; color:#fff" />
          </template>
          <b-dropdown-item v-clipboard="getTimelineUrl(timeline)">Copy link to profile</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item @click="doReport(timeline)">Report</b-dropdown-item>
        </b-dropdown>
      </div>
      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
          <b-img thumbnail rounded="circle" class="w-100 h-100" :src="timeline.avatar.filepath" :alt="timeline.slug" :title="timeline.name" />
        </router-link>
      </div>

      <div class="shareable-id">
        <b-card-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">{{ timeline.name }}</router-link>
          <fa-icon v-if="access_level==='premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">@{{ timeline.slug }}</router-link>
        </b-card-sub-title>
      </div>

      <slot></slot>

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
    timeline: null,
    access_level: null,
    created_at: null,
  },

  computed: {
    isLoading() {
      return !this.timeline || !this.access_level || !this.created_at
    },
  },

  data: () => ({
    moment: moment,
  }),

  methods: {
    renderSubscribe(selectedTimeline) {
      this.$log.debug('lists/WidgetTimeline.renderSubscribe() - emit', { selectedTimeline, });
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

    renderCancel(selectedTimeline, accessLevel) {
      // normally these attributes would be passed from server, but in this context we can determine them on client-side...
      selectedTimeline.is_following = true
      selectedTimeline.is_subscribed = (accessLevel==='premium')
      this.$log.debug('lists/WidgetTimeline.renderCancel() - emit', { selectedTimeline, });
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

    /*
    renderTip(selectedTimeline) { // to a timeline (user)
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: selectedTimeline,
          resource_type: 'timelines', 
        },
      })
    },
     */

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
  components: { },

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
</style>

