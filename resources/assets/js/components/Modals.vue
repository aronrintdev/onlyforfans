<template>
  <div>
    <b-modal
      id="modal-tip"
      size="sm"
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
      title="Follow"
      hide-footer
      body-class="p-0"
    >
      <FollowTimeline ref="followTimeline" :session_user="session_user" :timeline="selectedTimeline" :subscribe_only="subscribeOnly" />
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
  </div>
</template>

<script>
// Container for base modals
// %PSG: %NOTE %FIXME - not sure if refactoring all modals into a single component was the best idea, as it's possible
// one or more of the indiviudal modals could be used elsewhere in the app outside of timelines (eg, following list)
import Vuex from 'vuex';
import { eventBus } from '@/app'
import FollowTimeline from '@components/modals/FollowTimeline.vue'
import PurchasePost from '@components/modals/PurchasePost.vue'
import SendTip from '@components/modals/SendTip.vue'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'

export default {
  name: 'Modals',

  components: {
    FollowTimeline,
    PurchasePost,
    SendTip,
    PostDisplay,
    ImageDisplay
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'timeline', ]) // %TODO: may be able to drop timeline here (?)
  },

  data: () => ({
    references: {
      'modal-tip': 'sendTip',
      'modal-purchase-post': 'purchasePost',
      'modal-follow': 'followTimeline',
      'modal-post': 'postDisplay',
      'modal-photo': 'ImageDisplay',
    },
    selectedTimeline: null,
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
        }
      })

      this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
        console.log('Modals.vue', {
          bvEvent,
          modalId,
          references: this.references,
          refs: this.$refs,
        })
        if (this.references[modalId]) {
          if (typeof this.$refs[this.references[modalId]].modalHide === 'function') { // <<<==
            this.$refs[this.references[modalId]].modalHide(bvEvent)
          }
        }
      })
    },
  },

  created() {
    this.init()
  }
}
</script>

<style lang="scss" scoped>

</style>

