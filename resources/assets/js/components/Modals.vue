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
      id="modal-purchase-message"
      size="lg"
      title="Purchase Message"
      hide-footer
      body-class="p-0"
    >
      <PurchaseMessage ref="purchaseMessage" :message="selectedResource" />
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
      <div
        class="post-nav-arrows left"
        v-if="showPostArrows"
        @click="postModalAction('prev')"
      >
        <fa-icon :icon="['far', 'chevron-left']" size="lg" class="text-white" />
      </div>
      <PostDisplay ref="postDisplay" :session_user="session_user" :post="selectedResource" :is_feed="false" />
      <div
        class="post-nav-arrows right"
        v-if="showPostArrows"
        @click="postModalAction('next')"
      >
        <fa-icon :icon="['far', 'chevron-right']" size="lg" class="text-white" />
      </div>
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
      title="Scheduled Post"
      id="modal-schedule-datetime"
      size="md"
      hide-footer
      body-class="p-0"
    >
      <ScheduleDateTime ref="schedule_picker_modal" :scheduled_at="scheduled_at" :for_edit="is_for_edit" />
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

    <b-modal
      title="Expiration Period"
      id="expiration-period"
      hide-footer
      size="md"
      body-class="p-0"
    >
      <ExpirationPeriod ref="expirationPeriod" />
    </b-modal>

    <b-modal
      title="Start Promotion Campaign"
      id="modal-promotion-campaign"
      hide-footer
      size="lg"
      body-class="p-0"
    >
      <PromotionCampaign ref="promotionCampaign" />
    </b-modal>
  </div>
</template>

<script>
// Container for base modals
// %PSG: %NOTE %FIXME - not sure if refactoring all modals into a single component was the best idea, as it's possible
// one or more of the indiviudal modals could be used elsewhere in the app outside of timelines (eg, following list)
import Vuex from 'vuex';
import { eventBus } from '@/eventBus'
import FollowTimeline from '@components/modals/FollowTimeline.vue'
import CropImage from '@components/modals/CropImage.vue'
import PurchasePost from '@components/modals/PurchasePost.vue'
import SendTip from '@components/modals/SendTip.vue'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'
import ScheduleDateTime from '@components/modals/ScheduleDateTime.vue'
import EditPost from '@components/modals/EditPost.vue'
import ExpirationPeriod from '@components/modals/ExpirationPeriod.vue'
import PromotionCampaign from '@components/modals/PromotionCampaign.vue'
import PurchaseMessage from '@components/modals/PurchaseMessage'

export default {
  name: 'Modals',

  components: {
    FollowTimeline,
    CropImage,
    PurchasePost,
    PurchaseMessage,
    SendTip,
    PostDisplay,
    ImageDisplay,
    ScheduleDateTime,
    EditPost,
    ExpirationPeriod,
    PromotionCampaign,
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
      'expiration-period': 'expirationPeriod',
      'modal-promotion-campaign': 'promotionCampaign',
    },
    selectedTimeline: null,
    selectedUrl: null,
    selectedTimelineId: null,
    selectedResource: null,
    selectedResourceId: null,
    subscribeOnly: false,
    modalPayload: null,
    scheduled_at: null,
    is_for_edit: null,
    showPostArrows: false,
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
          case 'render-purchase-message':
            this.selectedResource = data.message
            this.$bvModal.show('modal-purchase-message')
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
            this.showPostArrows = data.showArrows
            this.$bvModal.show('modal-post')
            break
          case 'show-photo':
            this.selectedResource = data.mediafile
            this.$bvModal.show('modal-photo')
            break
          case 'show-schedule-datetime':
            this.scheduled_at = data.scheduled_at;
            this.is_for_edit = data.is_for_edit;
            this.$bvModal.show('modal-schedule-datetime')
            break
          case 'edit-post':
            this.selectedResource = data.post
            this.$bvModal.show('edit-post')
            break
          case 'expiration-period':
            this.$bvModal.show('expiration-period')
            break
          case 'modal-promotion-campaign':
            this.$bvModal.show('modal-promotion-campaign')
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
    postModalAction(action) {
      eventBus.$emit('post-modal-actions', action);
    }
  },

  created() {
    this.init()
  }
}
</script>

<style lang="scss" scoped>
.post-nav-arrows {
  position: absolute;
  width: 30px;
  height: 30px;
  top: calc(50vh - 90px);
  transform: translateY(-50%);
  border-radius: 50%;
  cursor: pointer;

  &:active {
    svg {
      color: rgba(255, 255, 255, 0.3) !important;
    }
  }

  &.left {
    left: -100px;
  }
  &.right {
    right: -100px;
  }
  svg {
    width: 100%;
    height: 100%;
  }
}
</style>

