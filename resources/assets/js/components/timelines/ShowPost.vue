<template>
  <div class="show_post-crate">
    <b-card header-tag="header" tag="article" class="superbox-post">
      <template #header>
        <div class="post-author">
          <section class="user-avatar">
            <a :href="'/'+post.timeline.username"><b-img :src="post.user.avatar.filepath" :alt="post.timeline.name" :title="post.timeline.name"></b-img></a>
          </section>
          <section class="user-post-details">
            <ul class="list-unstyled">
              <li>
                <a href="'/'+post.timeline.username" title="" data-toggle="tooltip" data-placement="top" class="username">{{ post.timeline.name }}</a>
                <span v-if="post.timeline.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
                <div class="small-text"></div>
              </li>
              <li>
                <timeago :datetime="post.created_at" :auto-update="60"></timeago>
                <span v-if="post.location" class="post-place"> at <a target="_blank" :href="'/get-location/'+post.location">
                    <b-icon icon="geo-fill" variant="primary" font-scale="1"></b-icon> {{ post.location }}</a>
                </span>
              </li>
            </ul>
          </section>
        </div>
      </template>
      <b-card-img v-if="post.mediafiles.length>0" :src="post.mediafiles[0].filepath" alt="Image"></b-card-img>
      <b-card-text>
        {{ post.description }}
      </b-card-text>
      <b-button href="#" variant="primary">Go somewhere</b-button>
    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex';
import ImageIcon from '../common/icons/ImageIcon.vue';
import CameraIcon from '../common/icons/CameraIcon.vue';
import EmojiIcon from '../common/icons/EmojiIcon.vue';
import MicIcon from '../common/icons/MicIcon.vue';
import LocationPinIcon from '../common/icons/LocationPinIcon.vue';
import TimerIcon from '../common/icons/TimerIcon.vue';
import CalendarIcon from '../common/icons/CalendarIcon.vue';

export default {

  props: {
    post: null,
  },

  computed: {
    //...Vuex.mapState(['session_user']),
  },

  data: () => ({

  }),

  created() {
  },

  methods: {

  },

  components: {
    EmojiIcon,
    ImageIcon,
    CameraIcon,
    MicIcon,
    LocationPinIcon,
    TimerIcon,
    CalendarIcon,
  },
}
</script>

<style scoped>
.superbox-post .post-author .user-avatar {
    width: 40px;
    height: 40px;
    float: left;
    margin-right: 10px;
}
.superbox-post .post-author .user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
}
.superbox-post .post-author .user-post-details ul {
    padding-left: 50px;
    margin-bottom: 0;
}
.superbox-post .post-author .user-post-details ul > li {
    color: #859AB5;
    font-size: 16px;
    font-weight: 400;
}
.superbox-post .post-author .user-post-details ul > li .username {
    text-transform: capitalize;
}

.superbox-post .post-author .user-post-details ul > li .post-time {
    color: #4a5568;
    font-size: 12px;
    letter-spacing: 0px;
    margin-right: 3px;
}
.superbox-post .post-author .user-post-details ul > li:last-child {
    font-size: 14px;
}
.superbox-post .post-author .user-post-details ul > li {
    color: #859AB5;
    font-size: 16px;
    font-weight: 400;
}
</style>
