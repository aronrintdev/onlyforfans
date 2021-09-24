<template>
  <div>
    <b-modal
      id="modal-vault-selector"
      size="lg"
      hide-header
      hide-footer
      body-class="p-0"
      @hide="closeModal"
    >
      <VaultSelectorModal @close="closeModal" :session_user="session_user" :payload="modalPayload" ref="selectFromVault" />
    </b-modal>

    <b-modal
      id="modal-tip"
      size="md"
      title="Send a Tip"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-tip')"
      :centered="mobile"
    >
      <SendTip ref="sendTip" :session_user="session_user" :payload="modalPayload" />
    </b-modal>

    <b-modal
      id="modal-purchase-post"
      size="lg"
      title="Purchase Post"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-purchase-post')"
      :centered="mobile"
    >
      <PurchasePost ref="purchasePost" :session_user="session_user" :post_id="selectedResourceId" />
    </b-modal>

    <b-modal
      id="modal-purchase-message"
      size="lg"
      title="Purchase Message"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-purchase-message')"
      :centered="mobile"
    >
      <PurchaseMessage ref="purchaseMessage" :message="selectedResource" :timeline="selectedTimeline" />
    </b-modal>

    <b-modal
      id="modal-follow"
      size="md"
      :title="followTimelineTitle"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-follow')"
      :centered="mobile"
    >
      <FollowTimeline ref="followTimeline" :session_user="session_user" :timeline="selectedTimeline" :subscribe_only="subscribeOnly" />
    </b-modal>

    <b-modal
      id="modal-crop"
      size="lg"
      title="Upload Avatar"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-crop')"
      :centered="mobile"
    >
      <CropImage ref="cropImage" :session_user="session_user" :url="selectedUrl" :timelineId="selectedTimelineId" />
    </b-modal>

    <b-modal
      id="modal-post"
      size="lg"
      hide-footer
      centered
      body-class="p-0"
      @hide="closeModal('modal-post')"
    >
      <div
        class="post-nav-arrows left"
        v-if="showPostArrows"
        @click="postModalAction('prev')"
      >
        <fa-icon :icon="['far', 'chevron-left']" size="lg" class="text-white" />
      </div>
      <div
        class="post-content"
        v-touch:swipe.top="() => postModalAction('next')"
        v-touch:swipe.bottom="() => postModalAction('prev')"
      >
        <PostDisplay
          ref="postDisplay"
          :session_user="session_user"
          :post="selectedResource"
          :key="selectedResource && selectedResource.id"
          :is_feed="false"
          :is_public_post="true"
          :imageIndex="imageIndex"
          @delete-post="deletePost"
        />
      </div>
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
      @hide="closeModal('modal-photo')"
      :centered="mobile"
    >
      <ImageDisplay ref="ImageDisplay" :session_user="session_user" :mediafile="selectedResource" :is_feed="false" />
    </b-modal>

    <b-modal
      title="Scheduled Post"
      id="modal-schedule-datetime"
      size="md"
      hide-footer
      body-class="p-0"
      @hide="closeModal('modal-schedule-datetime')"
      :centered="mobile"
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
      @hide="closeModal('edit-post')"
      :centered="mobile"
    >
      <EditPost ref="editPost" :post="selectedResource" />
    </b-modal>

    <b-modal
      id="report-post"
      size="lg"
      hide-header
      hide-footer
      body-class="p-0"
      no-close-on-backdrop
      @hide="closeModal('report-post')"
      :centered="mobile"
    >
      <ReportPost ref="reportPost" :post="selectedResource" />
    </b-modal>

    <b-modal
      title="Expiration Period"
      id="expiration-period"
      hide-footer
      size="md"
      body-class="p-0"
      @hide="closeModal('expiration-period')"
      :centered="mobile"
    >
      <ExpirationPeriod ref="expirationPeriod" />
    </b-modal>

    <b-modal
      title="Start Promotion Campaign"
      id="modal-promotion-campaign"
      hide-footer
      size="lg"
      body-class="p-0"
      @hide="closeModal('modal-promotion-campaign')"
      :centered="mobile"
    >
      <PromotionCampaign ref="promotionCampaign" :timeline="selectedTimeline" />
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
import VaultSelectorModal from '@components/modals/VaultSelector.vue'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'
import ScheduleDateTime from '@components/modals/ScheduleDateTime.vue'
import EditPost from '@components/modals/EditPost.vue'
import ReportPost from '@components/modals/ReportPost.vue'
import ExpirationPeriod from '@components/modals/ExpirationPeriod.vue'
import PromotionCampaign from '@components/modals/PromotionCampaign.vue'
import PurchaseMessage from '@components/modals/PurchaseMessage'
import { beforeDestroy } from 'vue2-dropzone';

export default {
  name: 'Modals',

  components: {
    FollowTimeline,
    CropImage,
    PurchasePost,
    PurchaseMessage,
    SendTip,
    VaultSelectorModal,
    PostDisplay,
    ImageDisplay,
    ScheduleDateTime,
    EditPost,
    ReportPost,
    ExpirationPeriod,
    PromotionCampaign,
  },

  computed: {
    ...Vuex.mapState([ 'session_user', 'timeline', 'mobile' ]), // %TODO: may be able to drop timeline here (?)
  },

  data: () => ({
    references: {
      'modal-vault-selector': 'selectFromVault',
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
    followTimelineTitle: '',
    imageIndex: 0,
    feedType: 'default',
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
            this.selectedTimeline = data.timeline
            this.$bvModal.show('modal-purchase-message')
            break

          case 'render-vault-selector':
            this.modalPayload = data
            this.$bvModal.show('modal-vault-selector')
            break

          case 'render-follow':
            this.selectedTimeline = data.timeline
            this.subscribeOnly = false
            this.followTimelineTitle = 'Follow'
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
            this.followTimelineTitle = 'Subscribe'
            this.$bvModal.show('modal-follow')
            break
          case 'render-tip':
            this.modalPayload = data
            this.$bvModal.show('modal-tip')
            break
          case 'show-post':
            this.selectedResource = data.post
            this.showPostArrows = data.showArrows
            this.imageIndex = data.imageIndex
            this.feedType = data.feedType
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
          case 'report-post':
            this.selectedResource = data.post
            this.$bvModal.show('report-post')
            break
          case 'expiration-period':
            this.$bvModal.show('expiration-period')
            break
          case 'modal-promotion-campaign':
            this.selectedTimeline = data.timeline
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
    },
    closeModal(modalId) {
      this.$bvModal.hide(modalId)
      eventBus.$emit('close-modal');
    },
    deletePost(postId) {
      const url = `/posts/${postId}`
      axios.delete(url)
        .then(() => {
          this.$bvToast.toast('Post was successfully removed.', {
            title: 'Success!',
            variant: 'success',
            solid: true,
            toaster: 'b-toaster-top-center',
          });
          eventBus.$emit('update-timelines', this.selectedResource.timeline.id)
          this.closeModal('modal-post')
        })
        .catch((err) => {
          this.$bvToast.toast(err.message, {
            variant: 'danger',
            title: 'Warning',
            solid: true,
            toaster: 'b-toaster-top-center',
          });
          this.closeModal('modal-post')
        })
    },
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
  top: 50%;
  transform: translateY(-50%);
  border-radius: 50%;
  cursor: pointer;
  z-index: 1000;

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

  @media (max-width: 576px) {
    & {
      background: rgba(0, 0, 0, 0.6);
      border-radius: 2px;
      padding: 5px;
      width: 30px;
      height: 40px;
      display: none;

      &.left {
        left: 0;
      }
      &.right {
        right: 0;
      }
    }
  }
}
</style>

<style lang="scss">
.modal-header {
  align-items: center;
}

#edit-post {
  padding: 0 !important;
}

#modal-post {

  .modal-header {
    height: 0;
    padding: 0;
    border: none;

    .close {
      position: absolute;
      z-index: 10;
      right: 18px;
      top: 25px;
      color: #343a40;
      opacity: 1;
      padding: 15px 15px;
    }
  }

  .post-header-tooltip {
    margin-right: 1.2em !important;
  }

  .superbox-post {
    height: calc(100vh - 60px);

    .post-crate-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;

      article {
        flex: 1;
        margin-bottom: 65px;
        overflow: hidden;

        .media-slider {
          height: 100%;

          .photoswipe-thumbnail, .v-photoswipe-thumbnail {
            // pointer-events: none;
            height: 100%;
            width: auto;
            margin: auto;
            max-width: 100%;
            object-fit: contain;
            position: relative;
            z-index: 2;
          }

          .single {
            height: 100%;
            position: relative;

            .wrap {
              position: relative;
              z-index: 1;
              max-width: 100vw;
              height: 100%;

              .video-js.vjs-fluid {
                width: 100%;
                max-width: 100%;
                max-height: 100%;
                height: 100%;
                padding-top: 0;
                background: transparent;
              }
            }

            .background-preview {
              background-color: rgba(0, 0, 0, 0.8);
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              z-index: 0;
              display: block;
              overflow: hidden;

              video, img {
                object-fit: cover;
                opacity: 0.4;
                transform: scale(1.1);
                height: 100%;
              }
            }
          }

          .multiple {
            height: 100%;

            .media-slider-swiper {
              height: 100%;

              .swiper-button-next,
              .swiper-button-prev {
                display: none;
              }

              .swiper-wrapper {
                align-items: center;

                @media (max-width: 576px) {
                  pointer-events: none;
                }

                // video {
                //   height: 100%;
                // }

                // .photoswipe-thumbnail, .v-photoswipe-thumbnail {
                //   pointer-events: none;
                //   height: 100%;
                //   width: auto;
                //   margin: auto;
                //   max-width: 100%;
                //   object-fit: contain;
                //   position: relative;
                //   z-index: 2;
                // }

                .background-preview {
                  background-color: rgba(0, 0, 0, 0.8);
                  position: absolute;
                  top: 0;
                  left: 0;
                  width: 100%;
                  height: 100%;
                  z-index: 0;
                  display: block;
                  overflow: hidden;

                  img {
                    object-fit: cover;
                    opacity: 0.4;
                    transform: scale(1.1);
                    height: 100%;
                  }
                }
              }
            }
          }
        }
      }
    }
    .card-footer {
      position: absolute;
      bottom: 0;
      background: #fff;
      width: 100%;
      min-height: 65px;
      z-index: 10;
      
      .collapse {
        max-height: 250px;
        overflow: auto;
      }
    }
  }

  @media (max-width: 576px) {
    height: 100%;

    .modal-header .close {
      padding-left: 15px;
      padding-right: 15px;
    }

    .modal-dialog {
      margin: 0;
      height: 100%;
      min-height: 100%;

      .modal-content {
        background: transparent;
        height: calc(100% - 40px);
      }
      .modal-body, .post-content, .post-crate {
        height: 100%;
      }
      .superbox-post {
        height: 100%;
        border: none;
        border-radius: 0;
      }
    }
  }
}
</style>
