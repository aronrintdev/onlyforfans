<template>
  <div>
    <b-modal
      id="modal-tip"
      size="lg"
      title="Send a Tip"
      hide-footer
      body-class="p-0"
    >
      <SendTip ref="sendTip" :session_user="session_user" :payload="modalPayload" />
    </b-modal>

    <b-modal
      id="modal-purchase-post"
      size="lg"
      title="Purchase Post"
      hide-footer
      body-class="p-0"
    >
      <PurchasePost ref="purchasePost" :session_user="session_user" :post_id="selectedResourceId" />
    </b-modal>

    <b-modal
      id="modal-follow"
      size="lg"
      title="Follow"
      hide-footer
      body-class="p-0"
    >
      <FollowTimeline ref="followTimeline" :session_user="session_user" :timeline="selectedTimeline" :subscribe_only="subscribeOnly" />
    </b-modal>

    <b-modal
      id="modal-crop"
      size="lg"
      title="Upload Avatar"
      hide-footer
      body-class="p-0"
    >
      <CropImage ref="cropImage" :session_user="session_user" :url="selectedUrl" :timelineId="selectedTimelineId" />
    </b-modal>

    <b-modal
      id="modal-post"
      size="xl"
      title="Post"
      hide-footer
      body-class="p-0"
    >
      <PostDisplay ref="postDisplay" :session_user="session_user" :post="selectedResource" :is_feed="false" />
    </b-modal>

    <b-modal
      size="xl"
      id="modal-photo"
      title="Photo"
      hide-footer
      body-class="p-0"
    >
      <ImageDisplay ref="ImageDisplay" :session_user="session_user" :mediafile="selectedResource" :is_feed="false" />
    </b-modal>
    <b-modal
      modal-class="schedule-message-modal"
      hide-header
      centered
      hide-footer
      id="modal-schedule-datetime"
    >
      <ScheduleDateTime ref="schedule_picker_modal" @apply="applySchedule" />
    </b-modal>

    <b-modal
      id="edit-post"
      size="lg"
      hide-header
      hide-footer
      body-class="p-0"
      no-close-on-backdrop
    >
      <EditPost ref="editPost" :post="selectedResource" />
    </b-modal>
  </div>
</template>

<script>
// Container for base modals
// %PSG: %NOTE %FIXME - not sure if refactoring all modals into a single component was the best idea, as it's possible
// one or more of the indiviudal modals could be used elsewhere in the app outside of timelines (eg, following list)
import Vuex from 'vuex';
import { eventBus } from '@/app'
import FollowTimeline from '@components/modals/FollowTimeline.vue'
import CropImage from '@components/modals/CropImage.vue'
import PurchasePost from '@components/modals/PurchasePost.vue'
import SendTip from '@components/modals/SendTip.vue'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'
import ScheduleDateTime from '@components/modals/ScheduleDateTime.vue'
import EditPost from '@components/modals/EditPost.vue'

export default {
  name: 'Modals',

  components: {
    FollowTimeline,
    CropImage,
    PurchasePost,
    SendTip,
    PostDisplay,
    ImageDisplay,
    ScheduleDateTime,
    EditPost
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'timeline', ]) // %TODO: may be able to drop timeline here (?)
  },

  data: () => ({
    references: {
      'modal-tip': 'sendTip',
      'modal-purchase-post': 'purchasePost',
      'modal-follow': 'followTimeline',
      'modal-crop': 'cropImage',
      'modal-post': 'postDisplay',
      'modal-photo': 'ImageDisplay',
      'modal-schedule-datetime': 'ScheduleDateTime',
      'edit-post': 'editPost',
    },
    selectedTimeline: null,
    selectedUrl: null,
    selectedTimelineId: null,
    selectedResource: null,
    selectedResourceId: null,
    subscribeOnly: false,
    modalPayload: null,
  }),

  methods: {
    init() {
      eventBus.$on('open-modal', ({ key, data }) => {
        this.$log.debug('views/timelines/Show.on(open-modal)', { key, data });
        switch(key) {
          case 'render-purchase-post':
            this.selectedResource = data.post
            this.selectedResourceId = data.post.id
            this.$bvModal.show('modal-purchase-post')
            break
          case 'render-follow':
            this.selectedTimeline = data.timeline
            this.subscribeOnly = false
            this.$bvModal.show('modal-follow')
            break
          case 'render-crop':
            this.selectedUrl = data.url
            this.selectedTimelineId = data.timelineId
            this.$bvModal.show('modal-crop')
            break
          case 'render-subscribe':
            this.selectedTimeline = data.timeline
            this.subscribeOnly = true
            this.$bvModal.show('modal-follow')
            break
          case 'render-tip':
            this.modalPayload = data
            this.$bvModal.show('modal-tip')
            break
          case 'show-post':
            this.selectedResource = data.post
            this.$bvModal.show('modal-post')
            break
          case 'show-photo':
            this.selectedResource = data.mediafile
            this.$bvModal.show('modal-photo')
            break
          case 'show-schedule-datetime':
            this.$bvModal.show('modal-schedule-datetime')
            break
          case 'edit-post':
            this.selectedResource = data.post
            this.$bvModal.show('edit-post')
            break
        }
      })

      this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
        if (this.references[modalId] && this.$refs[this.references[modalId]]) {
          if (typeof this.$refs[this.references[modalId]].modalHide === 'function') {
            this.$refs[this.references[modalId]].modalHide(bvEvent)
          }
        }
      })

    },
    applySchedule: function(data) {
      eventBus.$emit('apply-schedule', data)
    }
  },

  created() {
    this.init()
  }
}
</script>

<style lang="scss" scoped>

</style>

