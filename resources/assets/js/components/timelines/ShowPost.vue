<template>
  <div class="show_post-crate">

    <b-card header-tag="header" footer-tag="footer" tag="article" class="superbox-post" header-class="d-flex justify-content-between">

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
        <div v-if="session_user.id===post.user.id" class="post-ctrl">
          <b-dropdown id="dropdown-1" text="" class="m-md-2" variant="outline-dark">
            <b-dropdown-item @click="editPost()">Edit</b-dropdown-item>
            <b-dropdown-item @click="deletePost()">Delete</b-dropdown-item>
          </b-dropdown>
        </div>
      </template>

      <b-card-img v-if="post.mediafiles.length>0" :src="post.mediafiles[0].filepath" alt="Image"></b-card-img>

      <b-card-text>
        <p>{{ post.description }}</p>
      </b-card-text>

      <template #footer>
        <div class="panel-footer fans">

          <ul class="list-inline footer-ctrl">
            <li class="list-inline-item mr-3"><span @click="togglePostLike()" class="tag-clickable"><b-icon :icon="isPostLikedByMe?'heart-fill':'heart'" :variant="isPostLikedByMe?'danger':'default'" font-scale="1"></b-icon></span></li>
            <li class="list-inline-item mr-3"><span @click="toggleComments()" class="tag-clickable"><b-icon icon="chat-text" font-scale="1"></b-icon> ({{ this.commentCount }})</span></li>
            <li class="list-inline-item mr-3"><span @click="share()" class="tag-clickable"><b-icon icon="share" font-scale="1"></b-icon></span></li>
            <li class="list-inline-item mr-3"><span @click="tip()" class="tag-clickable">$</span></li>
          </ul>

          <ul v-if="renderComments" class="list-unstyled post-comments mt-3">
            <b-media tag="li" v-for="(c, idx) in post.comments" :key="c.id" class="mb-3">
              <template #aside>
                <b-img thumbnail fluid :src="c.user.avatar.filepath" alt="Avatar"></b-img>
              </template>
              <article class="OFF-d-flex">
                <span class="h5 tag-commenter mt-0 mb-1">{{ c.user.name }}</span>
                <span class="mb-0 tag-contents">{{ c.description }}</span>
              </article>
              <div class="d-flex comment-ctrl mt-1">
                <div @click="toggleCommentLike()"><b-icon icon="heart" font-scale="1"></b-icon> (0)</div>
                <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
                <div>Reply</div>
                <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
                <div><timeago :datetime="c.created_at" :auto-update="60"></timeago></div>
              </div>
            </b-media>
          </ul>

        </div>
      </template>

    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    //...Vuex.mapState(['session_user']),
  },

  data: () => ({
    renderComments: true, // false,
    isPostLikedByMe: false, // %FIXME INIT
    likeCount: 0, // %FIXME INIT
    commentCount: 0, // %FIXME INIT
  }),

  created() {
    //this.$store.dispatch('getMe');
  },

  methods: {

    async togglePostLike() {
      const url = `/posts/${this.post.id}/like`;
      const response = await axios.patch(url, { });
      console.log('response', { response });
      this.isPostLikedByMe = response.data.is_liked_by_session_user;
      this.likeCount = response.data.like_count;
    },

    toggleCommentLike() {
    },

    // %FIXME: optimize bandwidth by ajax call to get comments here instead of in initial index call (?)
    toggleComments() {
      this.renderComments = !this.renderComments;
    },

    share() {
    },

    tip() {
    },

    editPost() {
      // Check permissions...
      const is = this.session_user.id === this.post.user.id;
      console.log('ShowPost::editPost()', { is });
    },

    deletePost() {
      // Check permissions...
      const is = this.session_user.id === this.post.user.id;
      if (!is) {
        return;
      }
      this.$emit('delete-post', this.post.id );
      //this.$store.dispatch('deletePost', { postId: this.post.id });
    },

  },

  components: {
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

footer.card-footer ul.post-comments > li img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  padding: 0;
}
footer.card-footer ul.post-comments > li article {
  line-height: 1;
}
footer.card-footer ul.post-comments > li .tag-commenter {
  font-weight: 600;
  font-size: 14px;
  color: #2298F1;
  letter-spacing: 0px;
  text-transform: capitalize;
}

footer.card-footer ul.post-comments > li .tag-contents {
  font-weight: 400;
  font-size: 13px;
  color: #5B6B81;
  word-break: break-word;
}

footer.card-footer ul.post-comments .comment-ctrl {
  font-weight: 400;
  font-size: 12px;
  color: #859AB5;
  text-transform: capitalize;
}

.card-body p {
  font-size: 14px;
  font-weight: 400;
  color: #5B6B81;
  letter-spacing: 0.3px;
  margin-bottom: 0px;
  word-break: break-word;
}
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
