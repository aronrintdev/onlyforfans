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
          <span @click="toggleFavorite()" class="tag-clickable">
            <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
            <fa-icon v-else fixed-width :icon="['far', 'star']" class="clickable" style="font-size:1.2rem; color:#007bff" />
            <!--
            <b-icon :icon="true || isFavoritedByMe ? 'favorite-fill' : 'favorite'" variant="primary" font-scale="1" />
            -->
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
    isFavoritedByMe: false,
    loadingComments: false,
    // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() { 
    this.isLikedByMe = this.post.stats?.isLikedByMe || false
    this.likeCount = this.post.stats?.likeCount  || 0
    this.isFavoritedByMe = this.post.stats?.isFavoritedByMe || false
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
        data: { 
          resource: this.post,
          resource_type: 'posts', 
        },
      })
    },

    addComment(comment) {
      this.comments = [ ...this.comments, comment ]
      this.post.comments_count = this.post.comments_count + 1
    },

    async toggleFavorite() { // was toggleBookmark
      let response
      if (this.isFavoritedByMe) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'posts',
          favoritable_id: this.post.id,
        })
        this.isFavoritedByMe = false
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'posts',
          favoritable_id: this.post.id,
        })
        this.isFavoritedByMe = true
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
