<template>
  <b-media :class="isReply ? 'show_reply-box' : 'show_comment-crate'" :data-comment_guid="comment.id">

    <template #aside>
      <b-img thumbnail fluid :src="comment.user.avatar.filepath" alt="Avatar"></b-img>
    </template>

    <article>
      <span class="h5 tag-commenter mt-0 mb-1">{{ comment.user.name }}</span>
      <span class="mb-0 tag-contents">{{ comment.description }}</span>
    </article>

    <div class="d-flex comment-ctrl mt-1">
      <LikesButton :filled="isLikedByMe" show-count :count="comment.like_count" @toggled="toggleLike()" />
      <div class="mx-1">
        <b-icon icon="dot" font-scale="1" />
      </div>
      <div @click="toggleReplyFormVisibility()" class="tag-clickable">
        Reply ({{ replyCount }})
      </div>
      <div class="mx-1">
        <b-icon icon="dot" font-scale="1" />
      </div>
      <div>
        <timeago :datetime="comment.created_at" :auto-update="60" />
      </div>
    </div>

    <!-- replies -->
    <ul class="list-replies list-unstyled mt-1">
      <li v-for="(reply, index) in comment.replies" :key="reply.id" class="mb-3">
        <CommentDisplay
          isReply
          :comment="reply"
          :session_user="session_user"
          :post-id="postId"
          @toggle-reply-form-visibility="toggleReplyFormVisibility()"
          @updated="(value) => updateComment(value, index)"
        />
      </li>
    </ul>

    <!-- reply form -->
    <NewComment v-if="isReplyFormVisible" @submit="submitComment" class="mt-3" />

  </b-media>
</template>

<script>
import _ from 'lodash'
import Vuex from 'vuex';
// import ShowCommentReply from './ShowCommentReply.vue';
import LikesButton from '@components/common/LikesButton'
import NewComment from './New'

export default {
  name: 'CommentDisplay',
  components: {
    LikesButton,
    // ShowCommentReply,
    NewComment,
  },

  props: {
    comment: null,
    session_user: null,
    postId: null,
    isReply: { type: Boolean, default: false, },
  },

  computed: {
    replyCount() {
      return this.comment.replies_count || (this.comment.replies) ? this.comment.replies.length : 0
    }
  },

  data: () => ({

    isLikedByMe: false,

    isReplyFormVisible: false,

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
    addComment(value) {
      this.$emit('updated', {
        ...this.comment,
        replies_count: this.comment.replies_count + 1,
        replies: [ ...this.comment.replies, value ]
      })
    },

    updateComment(value, index) {
      var newReplies = _.cloneDeep(this.comment.replies)
      newReplies.splice(index, 1, value)
      this.$emit('updated', {
        ...this.comment,
        replies: newReplies
      })
    },

    toggleReplyFormVisibility() {
      this.isReplyFormVisible = !this.isReplyFormVisible
    },

    submitComment(form) { // reply to comment
      form.post_id = this.postId
      form.user_id = this.session_user.id
      form.parent_id = this.comment.id // %TODO
      this.axios.post(this.$apiRoute('comments.store'), form)
        .then(response => {
          // Add comment to list
          this.addComment(response.data.comment)
          this.isReplyFormVisible = false
        })
    },

    // like/unlike this comment (base comment or reply)
    /*
    toggleLike() {
      this.axios.patch(this.$apiRoute('comments.toggleLike', this.comment))
        .then(response => {
          this.$log.debug('response', { response })
          this.isCommentLikedByMe = response.data.is_liked_by_session_user
          this.likeCount = response.data.like_count
        })
    },
     */
    async toggleLike() { // for Comment
      let response
      if (this.isLikedByMe) {
        // unlike
        response = await axios.post(`/likeables/${this.session_user.id}`, {
          _method: 'delete',
          likeable_type: 'comments',
          likeable_id: this.comment.id,
        })
        this.isLikedByMe = false
      } else {
        // like
        response = await axios.put(`/likeables/${this.session_user.id}`, {
          likeable_type: 'comments',
          likeable_id: this.comment.id,
        })
        this.isLikedByMe = true
      }
      this.likeCount = response.data.like_count
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
