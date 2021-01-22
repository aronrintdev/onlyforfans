<template>
  <b-media class="show_reply-box" :data-comment_guid="reply.id">
    <template #aside>
      <b-img thumbnail fluid :src="reply.user.avatar.filepath" alt="Avatar"></b-img>
    </template>
    <article>
      <span class="h5 tag-commenter mt-0 mb-1">{{ reply.user.name }}</span>
      <span class="mb-0 tag-contents">{{ reply.description }}</span>
    </article>
    <div class="d-flex comment-ctrl mt-1">
      <div @click="toggleLike(reply.id)" class="tag-clickable"><b-icon :icon="isCommentLikedByMe?'heart-fill':'heart'" :variant="isCommentLikedByMe?'danger':'default'" font-scale="1"></b-icon> (0)</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div @click="toggleReplyFormVisibility()" class="tag-clickable">Reply</div>
      <div class="mx-1"><b-icon icon="dot" font-scale="1"></b-icon></div>
      <div><timeago :datetime="reply.created_at" :auto-update="60"></timeago></div>
    </div>
  </b-media>
</template>

<script>
export default {

  props: {
    reply: null,
    session_user: null,
    post_id: null,
  },

  computed: {
  },

  data: () => ({
    isCommentLikedByMe: false, // base comment - %FIXME INIT
  }),

  created() {
  },

  methods: {

    toggleReplyFormVisibility() {
      this.$emit('toggle-reply-form-visibility');
    },

    // like/unlike this comment (base comment or reply)
    async toggleLike(commentId) {
      console.log('ShowCommentReply.toggleLike', { commentId });
      const url = `/comments/${commentId}/like`;
      const response = await axios.patch(url, { });
      console.log('response', { response });
      this.isCommentLikedByMe = response.data.is_liked_by_session_user;
      this.likeCount = response.data.like_count;
    },

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
