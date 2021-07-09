<template>
  <div v-if="!isLoading" class="post-crate" v-bind:data-post_guid="post.id">
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
        <section class="d-flex align-items-center">
          <div class="expire_at" v-if="post.expire_at">
            <span class="text-secondary">{{ expireFromNow }}</span>
            <fa-icon :icon="['far', 'hourglass-half']" class="text-secondary ml-1 mr-2" />
          </div>
          <div v-if="session_user.id === post.user.id" class="post-ctrl mr-2">
            <b-dropdown right text="" class="m-md-2 post-header-menu" variant="outline-dark">
              <b-dropdown-item @click="showEditPost">
                <fa-icon icon="edit" fixed-width class="mr-2" />
                Edit
              </b-dropdown-item>
              <b-dropdown-item @click="showDeleteConfirmation = true">
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
            </b-dropdown>
          </div>
          <div @click="renderFull" v-if="is_feed" class="p-2 btn">
            <fa-icon icon="expand-alt" class="text-primary" size="2x" />
          </div>
          <b-btn v-if="displayClose" variant="link" class="text-secondary" @click="$emit('close')">
            <fa-icon icon="times" />
          </b-btn>
        </section>
      </template>

      <template v-if="post.access">
        <div :class="{ 'tag-has-mediafiles': hasMediafiles }" class="py-3 text-wrap">
          <b-card-text class="px-3 mb-0 tag-post_desc">{{ post.description }}</b-card-text>
        </div>
        <article v-if="hasMediafiles">
          <MediaSlider :mediafiles="post.mediafiles" :session_user="session_user" :use_mid="use_mid" />
        </article>
      </template>
      <template v-else>
        <PostCta :post="post" :session_user="session_user" :primary_mediafile="primaryMediafile" />
      </template>
    
      <template #footer>
        <PostFooter :post="post" :session_user="session_user" />
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
import { eventBus } from '@/app'
import PostHeader from './PostHeader'
import PostFooter from './PostFooter'
import PostCta from './PostCta'
import MediaSlider from './MediaSlider'

export default {
  components: {
    PostHeader,
    PostFooter,
    PostCta,
    MediaSlider,
  },

  props: {
    post: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    is_feed: { type: Boolean, default: true }, // is in context of a feed?
    displayClose: { type: Boolean, default: false }, // Display a close button in right corner of title?
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
    }
  },

  data: () => ({
    showDeleteConfirmation: false,
    showCopyToClipboardModal: false,
  }),

  mounted() { },

  created() {},

  methods: {

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
      const is = this.session_user.id === this.post.user.id // Check permissions
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
    }
  },

  watch: { },

}
</script>

<style scoped>
ul {
  margin: 0;
}

.feed-crate .superbox-post .card-text {
  color: #383838;
  white-space: no-wrap;
  overflow: hidden;
  max-height: 18rem;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 5;
  -webkit-box-orient: vertical;
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
