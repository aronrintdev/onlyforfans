<template>
  <div class="post-crate" v-bind:data-post_guid="post.id">
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
          <div v-if="session_user.id === post.user.id" class="post-ctrl mr-2">
            <b-dropdown id="dropdown-1" right text="" class="m-md-2" variant="outline-dark">
              <b-dropdown-item @click="editPost()">
                <fa-icon icon="edit" fixed-width class="mr-2" />
                Edit
              </b-dropdown-item>
              <b-dropdown-item @click="showDeleteConfirmation = true">
                <fa-icon icon="trash" fixed-width class="mr-2" />
                Delete
              </b-dropdown-item>
            </b-dropdown>
          </div>
          <div @click="renderFull" v-if="is_feed" class="p-2 btn">
              <b-icon icon="arrows-angle-expand" variant="primary" font-scale="1.2" />
          </div>
        </section>
      </template>

      <template v-if="post.access">
        <div :class="{ 'tag-has-mediafiles': hasMediafiles }" class="py-3 text-wrap">
          <b-card-text class="px-3 mb-0 tag-post_desc">{{ post.description }}</b-card-text>
        </div>
        <article v-if="hasMediafiles">
          <MediaSlider :post="post" :session_user="session_user" :use_mid="use_mid" />
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
      header-bg-variant="danger"
      header-text-variant="light"
      ok-variant="danger"
      size="sm"
      @ok="deletePost()"
    >
      <template #modal-title>
        <fa-icon icon="trash" fixed-width />
        {{ $t('delete.confirmation.title') }}
      </template>
      <div class="text-center" v-text="$t('delete.confirmation.message')" />
      <template #modal-ok>
        <fa-icon icon="trash" fixed-width />
        {{ $t('delete.confirmation.ok') }}
      </template>
    </b-modal>
  </div>
</template>

<script>
import Vuex from 'vuex'
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
  },

  data: () => ({
    showDeleteConfirmation: false,
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

    editPost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
      this.$router.push({ name: 'posts.edit', params: { slug: this.post.slug, post: this.post } })
    },

    deletePost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
      if (!is) {
        return
      }
      this.$emit('delete-post', this.post.id)
    },
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
        "title": "Delete Post?",
        "message": "Are you sure you want to delete this post?",
        "ok": "Delete Post"
      }
    }
  }
}
</i18n>
