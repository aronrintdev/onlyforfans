<template>
  <div v-if="!isLoading" class="post-crate" :id="post.id" v-bind:data-post_guid="post.id">
    <b-card
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-post"
      header-class="d-flex justify-content-between"
      no-body
    >
      <template #header>
        <PostHeader :post="post" :session_user="session_user"/>
        <section class="d-flex align-items-center m-0 post-header-tooltip">
          <div class="expire_at" v-if="post.expire_at">
            <span class="text-secondary">{{ expireFromNow }}</span>
            <fa-icon :icon="['far', 'hourglass-half']" class="text-secondary ml-1 mr-2" />
          </div>
          <div class="post-ctrl">
            <b-dropdown right text="" class="post-header-menu" toggle-class="text-secondary" variant="link">
              <b-dropdown-item v-if="isPostOwnedBySessionUser || canEditPostAsStaff" @click="showEditPost">
                <fa-icon icon="edit" fixed-width class="mr-2" />
                Edit
              </b-dropdown-item>
              <b-dropdown-item v-if="isPostOwnedBySessionUser || canDeletePostAsStaff" @click="showDeleteConfirmation = true">
                <fa-icon icon="trash" fixed-width class="mr-2" />
                Delete
              </b-dropdown-item>
              <b-dropdown-item @click="showCopyToClipboardModal = true">
                <fa-icon :icon="['fa', 'link']" fixed-width class="mr-2" />
                Copy link
              </b-dropdown-item>
              <b-dropdown-item>
                <ShareNetwork
                  network="facebook"
                  :url="postFullUrl"
                  title="AllFans"
                  hashtags="allfans"
                >
                  <fa-icon :icon="['fab', 'facebook-square']" fixed-width class="mr-2" />
                  Facebook Share
                </ShareNetwork>
              </b-dropdown-item>
              <b-dropdown-item>
                <ShareNetwork
                  network="twitter"
                  title="AllFans"
                  :url="postFullUrl"
                  hashtags="allfans"
                >
                  <fa-icon :icon="['fab', 'twitter-square']" fixed-width class="mr-2" />
                  Twitter Share
                </ShareNetwork>
              </b-dropdown-item>

              <template v-if="!isPostOwnedBySessionUser">
                <b-dropdown-divider />

                <b-dropdown-item @click="reportContent">
                  <fa-icon :icon="['fa', 'flag']" fixed-width class="mr-2" />
                  Report Content
                </b-dropdown-item>
              </template>

            </b-dropdown>
          </div>
          <!-- <div @click="renderFull" v-if="is_feed" class="p-2 btn">
            <fa-icon icon="expand-alt" class="text-primary" size="2x" />
          </div> -->
          <b-btn v-if="displayClose" variant="link" class="text-secondary" @click="$emit('close')">
            <fa-icon icon="times" />
          </b-btn>
        </section>
      </template>

      <div class="post-crate-content">
        <template v-if="post.access">
          <div v-if="post.description" v-touch:tap="tapHandler" :class="{ 'tag-has-mediafiles': hasMediafiles }" class="py-3 text-wrap">
            <b-card-text class="px-3 mb-0 tag-post_desc" :class="isCollapsed == 'expanded' ? 'full' : ''">
              <VueMarkdown :html="true" :source="parsedDescription" ref="markdownContent" @rendered="isCollapse" />
            </b-card-text>
            <b-btn class="collapse-btn" @click="changeCollapsable" variant="link" v-if="isCollapsed == 'collapsed'">{{ 'Read more' }}</b-btn>
            <b-btn class="collapse-btn" @click="changeCollapsable" variant="link" v-if="isCollapsed == 'expanded'">{{ 'Collapse' }}</b-btn>
          </div>
          <article v-if="hasMediafiles">
            <MediaSlider :post="post" :key="post.id" :mediafiles="post.mediafiles" :imageIndex="imageIndex" :session_user="session_user" :use_mid="use_mid" @doubleTap="tapHandler" />
          </article>
        </template>
        <template v-else>
          <PostCta :post="post" :session_user="session_user" :primary_mediafile="primaryMediafile" />
        </template>
        <div class="animation-box" :key="isLikedByMe" v-if="startLikeUnlikeAnime">
          <fa-icon
            :class="isLikedByMe ? 'text-danger' : 'text-secondary'"
            :icon="'heart'"
          />
        </div>
      </div>
    
      <template #footer>
        <PostFooter :post="post" :isLikedByMe="isLikedByMe" :likeCount="likeCount" :session_user="session_user" @toggleLike="toggleLike" />
      </template>

    </b-card>

    <b-modal
      v-model="showDeleteConfirmation"
      size="md"
      title="Delete Post"
    >
      <template #modal-title>
        {{ $t('delete.confirmation.title') }}
      </template>
      <div class="my-2 text-left" v-text="$t('delete.confirmation.message')" />
      <template #modal-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="showDeleteConfirmation=false">
            {{ $t('delete.confirmation.cancel') }}
          </b-btn>
          <b-btn class="px-3" variant="danger" @click="deletePost">
            {{ $t('delete.confirmation.ok') }}
          </b-btn>
        </div>
      </template>
    </b-modal>
    <b-modal
      v-model="showCopyToClipboardModal"
      size="md"
    >
      <template #modal-title>
        {{ $t('copytoclipboard.title') }}
      </template>
      <div class="my-2 text-left" v-text="postFullUrl" />
      <template #modal-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="showCopyToClipboardModal=false">
            {{ $t('copytoclipboard.cancel') }}
          </b-btn>
          <b-btn
            class="px-3"
            variant="primary"
            v-clipboard:copy="postFullUrl"
            v-clipboard:success="onCopySuccess"
            v-clipboard:error="onCopyError"
          >
            {{ $t('copytoclipboard.ok') }}
          </b-btn>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'
import { eventBus } from '@/eventBus'
import PostHeader from './PostHeader'
import PostFooter from './PostFooter'
import PostCta from './PostCta'
import MediaSlider from './MediaSlider'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

const MAX_LINES = 3;

export default {
  components: {
    PostHeader,
    PostFooter,
    PostCta,
    MediaSlider,
    VueMarkdown,
  },

  props: {
    post: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    is_feed: { type: Boolean, default: true }, // is in context of a feed?
    displayClose: { type: Boolean, default: false }, // Display a close button in right corner of title?
    is_public_post: { type: Boolean, default: false }, // is in context of a feed?
    imageIndex: 0,
  },

  computed: {
    username() {
      return this.post.user.username
    },
    hasMediafiles() {
      return this.post.mediafiles?.length > 0
    },
    primaryMediafile() {
      return this.hasMediafiles ? this.post.mediafiles[0] : null
    },
    isLoading() {
      return !this.post || !this.session_user
    },
    expireFromNow() {
      return moment.utc(this.post.expire_at).local().fromNow(true);
    },
    postFullUrl() {
      return `${window.location.origin}/posts/${this.post.slug}/details`;
    },
    isPostOwnedBySessionUser() {
      return this.post.user && this.session_user.id === this.post.user.id
    },
    canEditPostAsStaff() {
      const index = this.session_user.companies.findIndex(company => company.id == this.post.timeline.id);
      return index > -1 && this.session_user.companies[index].permissions && this.session_user.companies[index].permissions.findIndex(permission => permission.name   == 'Post.edit') > -1
    },
    canDeletePostAsStaff() {
      const index = this.session_user.companies.findIndex(company => company.id == this.post.timeline.id);
      return index > -1 && this.session_user.companies[index].permissions && this.session_user.companies[index].permissions.findIndex(permission => permission.name   == 'Post.delete') > -1
    },
    parsedDescription() {
      let text = this.post && this.post.description;
      text = `<span>${text}</span>`;
      const regexp = /\B@[\w\-.]+/g
      const htList = text.match(regexp) || [];
      htList.forEach(item => {
        text = text.replace(item, `</span><a href="/${item.slice(1)}">${item}</a><span>`);
      })
      return text;
    }
  },

  data: () => ({
    showDeleteConfirmation: false,
    showCopyToClipboardModal: false,
    isLikedByMe: false,
    startLikeUnlikeAnime: false,
    likeCount: 0,
    tapped: 0,
    isCollapsed: null,
  }),

  mounted() {
    this.isLikedByMe = this.post.stats?.isLikedByMe
    this.likeCount = this.post.stats?.likeCount
  },

  created() {},

  methods: {
    ...Vuex.mapMutations('posts', [ 'UPDATE_PUBLIC_POST', 'UPDATE_POST' ]),
    ...Vuex.mapMutations([ 'UPDATE_FEEDDATA_POST' ]),

    renderFull() {
      if (this.post.access) {
        eventBus.$emit('open-modal', { key: 'show-post', data: { post: this.post } })
      } else {
        if ( this.$options.filters.isSubscriberOnly(this.post) ) {
          eventBus.$emit('open-modal', { key: 'render-subscribe', data: { timeline: this.post.timeline } })
        } else if ( this.$options.filters.isPurchaseable(this.post) ) {
          eventBus.$emit('open-modal', { key: 'render-purchase-post', data: { post: this.post } })
        }
      }
    },
    showEditPost() {
      eventBus.$emit('open-modal', { key: 'edit-post', data: { post: this.post } })
    },

    deletePost() {
      const is = this.isPostOwnedBySessionUser || this.canDeletePostAsStaff // Check permissions
      if (!is) {
        return
      }
      this.$emit('delete-post', this.post.id)
    },
    onCopySuccess() {
      alert("Copy to Clipboard has been succeed");
      this.showCopyToClipboardModal = false;
    },
    onCopyError() {
      this.showCopyToClipboardModal = false;
      alert("Copy to Clipboard has been failed. Please try again later.");
    },

    reportContent() {
      eventBus.$emit('open-modal', { key: 'report-post', data: { post: this.post } })
    },

    async toggleLike() {
      let response;
        if (this.isLikedByMe) {
          // unlike
          response = await axios.post(`/likeables/${this.session_user.id}`, {
            _method: 'delete',
            likeable_type: 'posts',
            likeable_id: this.post.id,
          })
        } else {
          // like
          response = await axios.put(`/likeables/${this.session_user.id}`, {
            likeable_type: 'posts',
            likeable_id: this.post.id,
          })
        }
        this.isLikedByMe = !this.isLikedByMe
        this.likeCount = response.data.like_count
        const updatedPost = {
          ...this.post,
          stats: {
            ...this.post.stats,
            isLikedByMe: this.isLikedByMe,
            likeCount: this.likeCount,
          }
        }

        if (this.is_public_post) {
          // Update explore page post
          this.UPDATE_PUBLIC_POST({ post: updatedPost })
        } else {
          // Update timelines feeddata
          this.UPDATE_FEEDDATA_POST({ post: updatedPost })
        }
    },

    async tapHandler(e) {
      this.startLikeUnlikeAnime = false
      if (e) {
        this.tapped += 1;
        setTimeout(async () => {
          if (this.tapped == 2) {
            this.tapped = 0;
            await this.toggleLike();
            this.startLikeUnlikeAnime = true
            e.preventDefault();
          } else {
            this.tapped = 0;
          }
        }, 300);
      } else {
        await this.toggleLike();
        this.startLikeUnlikeAnime = true
      }
    }, 

    isCollapse() {
      this.$nextTick(() => {
        if ($(`#${this.post.id} .tag-post_desc`)[0] && $(`#${this.post.id} .tag-post_desc`)[0].scrollHeight > 24 * MAX_LINES) { // MAX_LINES = 3
          if ($(`#${this.post.id} .tag-post_desc`)[0].clientHeight < $(`#${this.post.id} .tag-post_desc`)[0].scrollHeight) {
            this.isCollapsed = 'collapsed';
            $(`#${this.post.id} .tag-post_desc`).height(72);
          } else {
            this.isCollapsed = 'expanded';
          }
        }
      })
    },

    changeCollapsable() {
      this.isCollapsed = this.isCollapsed == 'expanded' ? 'collapsed' : 'expanded';
    }
  },

  watch: { },

}
</script>

<style lang="scss" scoped>
ul {
  margin: 0;
}

.feed-crate .superbox-post .card-text {
  color: #383838;
  white-space: no-wrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;

  &.full {
    text-overflow: unset;
    -webkit-line-clamp: unset;
  }
}

.user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}

.user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}

.user-details ul > li .username {
  text-transform: capitalize;
}

.user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
.user-details ul > li:last-child {
  font-size: 14px;
}

.post-crate-content {
  position: relative;

  ::v-deep p:last-child {
    margin: 0 !important;
  }
}

.post-crate-content-eventbox {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.01);
  z-index: 11;
}

.animation-box {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
  
  svg {
    animation-duration: 1500ms;
    animation-name: like-heart-animation;
    animation-timing-function: ease-in-out;
    margin: 0 auto;
    opacity: 0;
    width: 80px;
    height: auto;
    transform: scale(0);
    animation-play-state: initial;
  }
}

@keyframes like-heart-animation {
  0% {
    opacity: 0;
    transform: scale(0);
  }

  40% {
    opacity: 1;
    transform: scale(1);
  }

  70% {
    opacity: 1;
    transform: scale(1);
  }

  100% {
    opacity: 0;
    transform: scale(0);
  }
}

.post-header-tooltip {
  margin-right: -0.8em !important;
}

.tag-post_desc + .collapse-btn {
  margin: 0 16px;
  padding: 0;
  font-size: 15px;
  outline: none;
  box-shadow: none;
}

</style>

<i18n lang="json5">
{
  "en": {
    "delete": {
      "confirmation": {
        "title": "Confirm delete",
        "message": "Are you sure you want to delete this post?",
        "ok": "Delete Post",
        "cancel": "Cancel"
      }
    },
    "copytoclipboard": {
      "title": "Copy link of the post",
      "ok": "Copy",
      "cancel": "Cancel"
    }
  }
}
</i18n>
