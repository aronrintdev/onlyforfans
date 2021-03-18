<template>
  <div class="post-crate" v-bind:data-post_guid="post.id">
    <b-card
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-post"
      header-class="d-flex justify-content-between"
    >
      <template #header>
        <PostHeader :post="post" :session_user="session_user"/>
        <div v-if="session_user.id === post.user.id" class="post-ctrl">
          <b-dropdown id="dropdown-1" text="" class="m-md-2" variant="outline-dark">
            <b-dropdown-item @click="editPost()">Edit</b-dropdown-item>
            <b-dropdown-item @click="deletePost()">Delete</b-dropdown-item>
          </b-dropdown>
        </div>
      </template>

      <template v-if="post.access">
        <article v-if="hasMediafiles">
          <b-card-img v-if="primaryMediafile.is_image" :src="primaryMediafile.filepath" alt="Image" ></b-card-img>
          <b-embed v-if="primaryMediafile.is_video" type="video" controls poster="poster.png">
            <source :src="primaryMediafile.filepath" type="video/webm">
            <source :src="primaryMediafile.filepath" type="video/mp4">
          </b-embed>
        </article>
        <b-card-text> <p>{{ post.description }}</p> </b-card-text>
      </template>
      <template v-else-if="$options.filters.isSubscriberOnly(post)">
        <article class="locked-content d-flex justify-content-center align-items-center">
          <div class="d-flex flex-column">
            <b-icon icon="lock-fill" font-scale="5" variant="light" />
            <b-button @click="renderSubscribe" class="mt-3" variant="primary">Subscribe</b-button>
          </div>
        </article>
      </template>
      <template v-else>
        <article class="locked-content d-flex justify-content-center align-items-center">
          <div class="d-flex flex-column">
            <b-icon icon="lock-fill" font-scale="5" variant="light" />
            <b-button @click="renderPurchasePost" class="mt-3" variant="primary">Buy</b-button>
          </div>
        </article>
      </template>

      <template #footer>
        <PostFooter :post="post" :session_user="session_user" />
      </template>
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import PostHeader from './PostHeader'
import PostFooter from './PostFooter'

export default {
  components: {
    PostHeader,
    PostFooter,
  },

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    username() {
      return this.post.user.username
    },
    hasMediafiles() {
      return this.post.mediafiles?.length > 0
    },
    primaryMediafile() {
      //return this.hasMediafiles ? this.post.mediafiles[0].filepath : null
      return this.hasMediafiles ? this.post.mediafiles[0] : null
    },
    isLoading() {
      return !this.post || !this.session_user
    },
  },

  data: () => ({
  }),

  mounted() { },

  created() {},

  methods: {

    share() {},

    tip() {},

    editPost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
    },

    renderPurchasePost() {
      //this.$emit('render-purchase-post', this.post)
      //this.$bvModal.show('modal-purchase_post', this.post)
      //eventBus.$emit('render-purchase-post-modal', this.post)
      eventBus.$emit('open-modal', {
        key: 'render-purchase-post', 
        data: {
          post: this.post,
        }
      })
    },

    renderSubscribe() {
      //this.$emit('render-subcribe', this.post)
      //eventBus.$emit('render-subscribe-modal', this.post)
      //this.$bvModal.show('modal-follow', this.post.postable_id) // = timeline id
      eventBus.$emit('open-modal', {
        key: 'render-subscribe', 
        data: {
          //timeline_id: this.post.postable_id,
          timeline: this.post.timeline,
        }
      })
    },

    deletePost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
      if (!is) {
        return
      }
      this.$emit('delete-post', this.post.id)
    },
  },

  watch: {
  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}

.card-body p {
  font-size: 14px;
  font-weight: 400;
  color: #5b6b81;
  letter-spacing: 0.3px;
  margin-bottom: 0px;
  word-break: break-word;
}
body .user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
body .user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}
body .user-details ul > li .username {
  text-transform: capitalize;
}

body .user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
body .user-details ul > li:last-child {
  font-size: 14px;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}
body .locked-content {
  background: url('/images/locked_post.png') center center no-repeat !important;
  background-size: auto;
  background-size: cover !important;
  min-height: 20rem;
}
</style>
