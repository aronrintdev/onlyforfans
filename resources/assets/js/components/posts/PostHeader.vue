<template>
  <div class="post-author">
    <section class="user-avatar">
      <router-link :to="timelineRoute">
        <b-img
          :src="post.user.avatar.filepath"
          :alt="post.user.name"
          :title="post.user.name"
        ></b-img>
      </router-link>
    </section>
    <section class="user-details">
      <ul class="list-unstyled">
        <li>
          <router-link
            :to="timelineRoute"
            title=""
            data-toggle="tooltip"
            data-placement="top"
            class="username"
            v-text="post.user.name"
          />
          <span v-if="post.user.verified" class="verified-badge">
            <b-icon icon="check-circle-fill" variant="success" font-scale="1" />
          </span>
        </li>
        <li>
          <timeago :datetime="post.created_at" :auto-update="60"></timeago>
          <span v-if="post.location" class="post-place">
            at
            <a target="_blank" :href="`/get-location/${post.location}`">
              <b-icon icon="geo-fill" variant="primary" font-scale="1" />
              {{ post.location }}
            </a>
          </span>
        </li>
      </ul>
    </section>
    <div class="tag-debug">
      <ul>
        <li>ID: {{ post.id | niceGuid }}</li>
        <li>Type: {{ post.type | enumPostType }}</li>
        <li>Price: {{ post.price }}</li>
        <li>Access: {{ post.access }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
//import { eventBus } from '@/app'

export default {

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
    timelineRoute() {
      return {
        name: 'timeline.show',
        params: { slug: this.post.timeline_slug }
      }
    },
  },

  data: () => ({
  }),
}

</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header {
  background-color: #fff;
}

/*
.card-body p {
  font-size: 14px;
  font-weight: 400;
  color: #5b6b81;
  letter-spacing: 0.3px;
  margin-bottom: 0px;
  word-break: break-word;
}
 */
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
.tag-debug {
  display: none;
  /*
   */
}
</style>
