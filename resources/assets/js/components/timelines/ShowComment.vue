<template>
  <b-media class="show_comment-crate" :data-comment_guid="comment.id">

    <template #aside>
      <b-img thumbnail fluid :src="comment.user.avatar.filepath" alt="Avatar"></b-img>
    </template>

    <article>
      <span class="h5 tag-commenter mt-0 mb-1">{{ comment.user.name }}</span>
      <span class="mb-0 tag-contents">{{ comment.description }}</span>
    </article>

    <div class="d-flex comment-ctrl mt-1">
      <div @click="toggleLike(comment.id)" class="tag-clickable"><b-icon :icon="isCommentLikedByMe?'heart-fill':'heart'" :variant="isCommentLikedByMe?'danger':'default'" font-scale="1"></b-icon> (0)</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div @click="toggleReplyFormVisibility()" class="tag-clickable">Reply</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div><timeago :datetime="comment.created_at" :auto-update="60"></timeago></div>
    </div>

    <!-- replies -->
    <ul class="list-replies list-unstyled mt-1">
      <li v-for="(r, idx) in comment.replies" :key="r.id" class="mb-3">
        <ShowCommentReply :reply="r" :session_user="session_user" :post_id="post_id" v-on:toggle-reply-form-visibility="toggleReplyFormVisibility()" />
      </li>
    </ul>

    <!-- reply form -->
    <b-form v-if="isReplyFormVisible" @submit="submitComment" >
      <b-form-input class="new-comment" v-model="newCommentForm.description" placeholder="Write a comment...press enter to post"></b-form-input>
    </b-form>

  </b-media>
</template>

<script>
import Vuex from 'vuex';
import ShowCommentReply from './ShowCommentReply.vue';

export default {

  props: {
    comment: null,
    session_user: null,
    post_id: null,
  },

  computed: {
  },

  data: () => ({

    isReplyFormVisible: false,

    isCommentLikedByMe: false, // base comment - %FIXME INIT

    newCommentForm: {
      post_id: null,
      user_id: null,
      parent_id: null,
      description: '',
      // %TODO: attach mediafiles to comments
    },
  }),

  created() {
  },

  methods: {

    toggleReplyFormVisibility() {
      this.isReplyFormVisible = !this.isReplyFormVisible;
    },

    async submitComment(e) { // reply to comment
      e.preventDefault();
      this.newCommentForm.post_id = this.post_id;
      this.newCommentForm.user_id = this.session_user.id;
      this.newCommentForm.parent_id = this.comment.id; // %TODO
      const response = await axios.post(`/comments`, this.newCommentForm);

      // reset form
      this.newCommentForm.post_id = null;
      this.newCommentForm.user_id = null;
      this.newCommentForm.parent_id = null;
      this.newCommentForm.description = '';
    },

    // like/unlike this comment (base comment or reply)
    async toggleLike(commentId) {
      const url = `/comments/${commentId}/like`;
      const response = await axios.patch(url, { });
      console.log('response', { response });
      this.isCommentLikedByMe = response.data.is_liked_by_session_user;
      this.likeCount = response.data.like_count;
    },

  },

  components: {
    ShowCommentReply,
  },
}
</script>

<style scoped>
footer.card-footer .post-comments ul > li img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  padding: 0;
}
footer.card-footer .post-comments ul > li article {
  line-height: 1;
}
footer.card-footer .post-comments ul > li .tag-commenter {
  font-weight: 600;
  font-size: 14px;
  color: #2298F1;
  letter-spacing: 0px;
  text-transform: capitalize;
}

footer.card-footer .post-comments ul > li .tag-contents {
  font-weight: 400;
  font-size: 13px;
  color: #5B6B81;
  word-break: break-word;
}

footer.card-footer .post-comments ul .comment-ctrl {
  font-weight: 400;
  font-size: 12px;
  color: #859AB5;
  text-transform: capitalize;
}
</style>
