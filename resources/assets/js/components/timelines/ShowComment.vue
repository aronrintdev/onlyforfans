<template>
  <b-media class="show_comment-crate">

    <template #aside>
      <b-img thumbnail fluid :src="comment.user.avatar.filepath" alt="Avatar"></b-img>
    </template>

    <article class="OFF-d-flex">
      <span class="h5 tag-commenter mt-0 mb-1">{{ comment.user.name }}</span>
      <span class="mb-0 tag-contents">{{ comment.description }}</span>
    </article>

    <div class="d-flex comment-ctrl mt-1">
      <div @click="toggleCommentLike()"><b-icon icon="heart" font-scale="1"></b-icon> (0)</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div>Reply</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div><timeago :datetime="comment.created_at" :auto-update="60"></timeago></div>
    </div>

    <!-- replies -->
    <ul class="list-unstyled mt-1">
      <li v-for="(r, idx) in comment.replies" :key="r.id" class="mb-3">
        <b-media class="show_reply-box">
          <template #aside>
            <b-img thumbnail fluid :src="r.user.avatar.filepath" alt="Avatar"></b-img>
          </template>
          <article class="OFF-d-flex">
            <span class="h5 tag-commenter mt-0 mb-1">{{ r.user.name }}</span>
            <span class="mb-0 tag-contents">{{ r.description }}</span>
          </article>
          <div class="d-flex comment-ctrl mt-1">
            <div @click="toggleReplyLike()"><b-icon icon="heart" font-scale="1"></b-icon> (0)</div>
            <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
            <div>Reply</div>
            <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
            <div><timeago :datetime="r.created_at" :auto-update="60"></timeago></div>
          </div>
        </b-media>
      </li>
    </ul>

    <!-- reply form -->
    <b-form @submit="submitComment" >
      <b-form-input class="new-comment" v-model="newCommentForm.description" placeholder="Write a comment...press enter to post"></b-form-input>
    </b-form>

  </b-media>
</template>

<script>
import Vuex from 'vuex';

export default {

  props: {
    comment: null,
    session_user: null,
    post_id: null,
  },

  computed: {
  },

  data: () => ({
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

    toggleReplyLike() {
    },

  },

  components: {
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
