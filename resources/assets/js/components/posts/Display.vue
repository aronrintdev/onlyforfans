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
        <div v-if="session_user.id === post.user.id" class="post-ctrl">
          <b-dropdown id="dropdown-1" text="" class="m-md-2" variant="outline-dark">
            <b-dropdown-item @click="editPost()">Edit</b-dropdown-item>
            <b-dropdown-item @click="deletePost()">Delete</b-dropdown-item>
          </b-dropdown>
        </div>
      </template>

      <template v-if="post.access">
        <b-card-text> <p class="mb-3">{{ post.description }}</p> </b-card-text>
        <article v-if="hasMediafiles">
          <MediaSlider :post="post" :session_user="session_user" />
          <b-card-img v-if="false && primaryMediafile.is_image" :src="primaryMediafile.filepath" alt="Image" ></b-card-img>
          <b-embed v-if="false && primaryMediafile.is_video" type="video" controls poster="poster.png">
            <source :src="primaryMediafile.filepath" type="video/webm">
            <source :src="primaryMediafile.filepath" type="video/mp4">
          </b-embed>
        </article>
      </template>
      <template v-else>
        <PostCta :post="post" :session_user="session_user" />
      </template>

      <template #footer>
        <PostFooter :post="post" :session_user="session_user" />
      </template>

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
//import { eventBus } from '@/app'
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

    editPost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
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

body .card-body p {
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
</style>
