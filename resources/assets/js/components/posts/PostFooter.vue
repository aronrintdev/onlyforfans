<template>
  <div class="panel-footer fans">

    <div class="d-flex justify-content-between">
      <ul class="d-flex list-inline footer-ctrl mb-0">
        <li class="mr-3">
          <LikesButton @toggled="toggleLike()" :filled="isLikedByMe" :count="likeCount" />
        </li>
        <li class="mr-3">
          <span @click="toggleComments()" class="tag-clickable">
            <b-icon icon="chat-text" font-scale="1" />
          </span>
        </li>
        <li class="mr-3">
          <span @click="share()" class="tag-clickable">
            <b-icon icon="share" font-scale="1" />
          </span>
        </li>
        <li class="mr-3">
          <span @click="renderTip" class="tag-clickable">$</span> <!-- %TODO: replace with font-awesome icon -->
        </li>
      </ul>
      <ul class="d-flex list-inline footer-ctrl mb-0">
        <li class="mr-3">
          <span @click="toggleBookmark()" class="tag-clickable">
            <b-icon :icon="isBookmarkedByMe ? 'bookmark-fill' : 'bookmark'" variant="primary" font-scale="1" />
          </span>
        </li>
      </ul>
    </div>

    <div class="like-count">
      <template v-if="likeCount===1"><span class="mr-2">{{ likeCount }} like</span></template>
      <template v-if="likeCount > 1"><span class="mr-2">{{ likeCount }} likes</span></template>
      <template v-if="post.stats.commentCount===1"><span class="mr-2">{{ post.stats.commentCount }} comment</span></template>
      <template v-if="post.stats.commentCount > 1"><span class="mr-2">{{ post.stats.commentCount }} comments</span></template>
    </div>

    <b-collapse v-model="renderComments">
      <CommentList
        :post-id="post.id"
        :loading="loadingComments"
        v-model="comments"
      />
    </b-collapse>

  </div>
</template>

<script>
import { eventBus } from '@/app'
import CommentList from '@components/comments/List'
import CommentDisplay from '@components/comments/Display'
import NewComment from '@components/comments/New'
import LikesButton from '@components/common/LikesButton'

export default {
  components: { 
    CommentList,
    CommentDisplay,
    NewComment,
    LikesButton,
  },

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
  },

  data: () => ({
    comments: [], // %NOTE: rendered comments are loaded dynamically as they contain additional relation data,
    renderComments: false,
    isLikedByMe: false,
    likeCount: 0, // %FIXME INIT
    isBookmarkedByMe: false,
    loadingComments: false,
    // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() { 
    this.isLikedByMe = this.post.stats?.isLikedByMe || false
    this.likeCount = this.post.stats?.likeCount  || 0
    this.isBookmarkedByMe = this.post.stats?.isBookmarkedByMe || false
  },

  created() {},

  methods: {
    async toggleLike() { // for Post
      let response
      if (this.isLikedByMe) {
        // unlike
        response = await axios.post(`/likeables/${this.session_user.id}`, {
          _method: 'delete',
          likeable_type: 'posts',
          likeable_id: this.post.id,
        })
        this.isLikedByMe = false
      } else {
        // like
        response = await axios.put(`/likeables/${this.session_user.id}`, {
          likeable_type: 'posts',
          likeable_id: this.post.id,
        })
        this.isLikedByMe = true
      }
      this.likeCount = response.data.like_count
    },

    share() {},

    renderTip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip', 
        data: { resource_type: 'posts', resource_id: this.post.id },
      })
    },

    addComment(comment) {
      this.comments = [ ...this.comments, comment ]
      this.post.comments_count = this.post.comments_count + 1
    },

    async toggleBookmark() {
      let response
      if (this.isBookmarkedByMe) { // remove
        response = await axios.post(`/bookmarks/remove`, {
          bookmarkable_type: 'posts',
          bookmarkable_id: this.post.id,
        })
        this.isBookmarkedByMe = false
      } else { // add
        response = await axios.post(`/bookmarks`, {
          bookmarkable_type: 'posts',
          bookmarkable_id: this.post.id,
        })
        this.isBookmarkedByMe = true
      }
    },

    updateCommentsCount(value, index) {
      this.comments = this.comments.splice(index, 1, value)
    },

    toggleComments() {
      const isCurrentlyVisible = !!this.renderComments
      if (isCurrentlyVisible) {
        this.renderComments = false // toggle -> hide
      } else if ( this.post.access ) {
        if ( this.comments ) {
          this.renderComments = true // Some stored comments, show comments while loading
        }
        this.loadComments()
      }
    },

    loadComments() {
      if (!this.loadingComments) {
        this.loadingComments = true
        this.axios.get( this.$apiRoute('posts.indexComments', this.post))
          .then(response => {
            this.comments = response.data.comments
            this.renderComments = true // toggle -> show
            this.loadingComments = false
          })
      }
    },
  },

  watch: {
    comments(value, oldValue) {
      if (typeof value === 'undefined' ) {
        this.comments = []
        return
      }
      if (value.length > 0 && value.length !== oldValue.length) {
        this.post.comments_count = value.length
      }
    }
  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

.like-count {
  font-size: 0.80rem;
}

</style>
