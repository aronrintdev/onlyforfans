<template>
  <div class="show_post-crate" v-bind:data-post_guid="post.id">

    <b-card header-tag="header" footer-tag="footer" tag="article" class="superbox-post" header-class="d-flex justify-content-between">

      <template #header>
        <div class="post-author">
          <section class="user-avatar">
            <a :href="timelineUrl"><b-img :src="post.user.avatar.filepath" :alt="post.user.name" :title="post.user.name"></b-img></a>
          </section>
          <section class="user-details">
            <ul class="list-unstyled">
              <li>
                <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ post.user.name }}</a>
                <span v-if="post.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
                <div class="small-text"></div>
              </li>
              <li>
                <timeago :datetime="post.created_at" :auto-update="60"></timeago>
                <span v-if="post.location" class="post-place"> at <a target="_blank" :href="`/get-location/${post.location}`">
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
            <li class="list-inline-item mr-3"><span @click="togglePostLike()" class="tag-clickable"><b-icon :icon="isLikedByMe?'heart-fill':'heart'" :variant="isLikedByMe?'danger':'default'" font-scale="1"></b-icon></span></li>
            <li class="list-inline-item mr-3"><span @click="toggleComments()" class="tag-clickable"><b-icon icon="chat-text" font-scale="1"></b-icon> ({{ post.comments.length }})</span></li>
            <li class="list-inline-item mr-3"><span @click="share()" class="tag-clickable"><b-icon icon="share" font-scale="1"></b-icon></span></li>
            <li class="list-inline-item mr-3"><span @click="tip()" class="tag-clickable">$</span></li>
          </ul>

          <section v-if="renderComments" class="post-comments mt-3">
            <b-form @submit="submitComment" >
              <b-form-input class="new-comment" v-model="newCommentForm.description" placeholder="Write a comment...press enter to post"></b-form-input>
            </b-form>
            <ul class="list-basecomments list-unstyled mt-1">
              <li v-for="(c, idx) in comments" :key="c.id" class="mb-3">
                <ShowComment :comment="c" :session_user="session_user" :post_id="post.id" />
              </li>
            </ul>
          </section>

        </div>
      </template>

    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex';
import ShowComment from './ShowComment.vue';

export default {

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    timelineUrl(username) {
      return `/timelines/${this.post.user.username}`; // DEBUG
      //return `/${this.post.user.username}`;
    },
  },

  data: () => ({
    newCommentForm: {
      post_id: null,
      user_id: null,
      parent_id: null,
      description: '',
      // %TODO: attach mediafiles to comments
    },
    renderComments: false,
    isLikedByMe: false, // this.post.isLikedByMe,
    likeCount: 0, // %FIXME INIT
    comments: [], // %NOTE: rendered comments are loaded dynamically as they contain additional relation data,
                  // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() {
    this.isLikedByMe = this.post.isLikedByMe;
  },

  created() {
  },

  methods: {

    async togglePostLike() {
      let response;
      if ( this.isLikedByMe ) { // unlike
        response = await axios.post(`/likeables/${this.session_user.id}`, { 
          _method: 'delete',
          likeable_type: 'posts',
          likeable_id: this.post.id,
        });
        this.isLikedByMe = false;
      } else { // like
        response = await axios.put(`/likeables/${this.session_user.id}`, { 
          likeable_type: 'posts',
          likeable_id: this.post.id,
        });
        this.isLikedByMe = true;
      }
      this.likeCount = response.data.like_count;
    },

    toggleCommentLike() {
    },

    // %FIXME: optimize bandwidth by ajax call to get comments here instead of in initial index call (?)
    async toggleComments() {
      const isCurrentlyVisible = !!this.renderComments;
      if ( isCurrentlyVisible ) {
        this.renderComments = false; // toggle -> hide
      } else {
        const response = await axios.get(`/comments`, { 
          params: {
            post_id: this.post.id,
          },
        });
        console.log('comments', { response });
        //this.comments = response.comments;
        this.comments = response.data.comments;
        this.renderComments = true; // toggle -> show
      }
    },

    share() {
    },

    tip() {
    },

    async submitComment(e) {
      e.preventDefault();
      this.newCommentForm.post_id = this.post.id;
      this.newCommentForm.user_id = this.session_user.id;
      this.newCommentForm.parent_id = null; // %TODO
      const response = await axios.post(`/comments`, this.newCommentForm);

      // reset form
      this.newCommentForm.post_id = null;
      this.newCommentForm.user_id = null;
      this.newCommentForm.parent_id = null;
      this.newCommentForm.description = '';
    },

    editPost() {
      const is = this.session_user.id === this.post.user.id; // Check permissions
      console.log('ShowPost::editPost()', { is });
    },

    deletePost() {
      const is = this.session_user.id === this.post.user.id; // Check permissions
      if (!is) {
        return;
      }
      this.$emit('delete-post', this.post.id );
    },

  },

  components: {
    ShowComment,
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
  color: #5B6B81;
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
  color: #859AB5;
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
  color: #859AB5;
  font-size: 16px;
  font-weight: 400;
}
</style>
