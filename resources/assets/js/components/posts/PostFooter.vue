<template>
  <div class="panel-footer fans">
    <div v-if="renderComments" @click="toggleComments" class="comments-box-bg"></div>
    <div class="d-flex justify-content-between">
      <ul class="d-flex list-inline footer-ctrl mb-0">
        <li class="mr-3">
          <LikesButton @toggled="toggleLike()" :filled="isLikedByMe" :count="likeCount" />
        </li>
        <li class="mr-3">
          <span @click="toggleComments()" class="tag-clickable">
            <fa-icon size="lg" :icon="['far', 'comments']" class="text-secondary" />
          </span>
        </li>
        <!-- hide until implemented
        <li class="mr-3">
          <span @click="share()" class="tag-clickable">
            <fa-icon size="lg" :icon="['far', 'share-square']" class="text-secondary" />
          </span>
        </li>
        -->
        <li class="mr-3">
          <span @click="renderTip" class="tag-clickable">
            <fa-icon size="lg" icon="dollar-sign" class="text-secondary" />
          </span>
        </li>
      </ul>
      <ul class="d-flex list-inline footer-ctrl mb-0">
        <li class="ml-3">
          <span @click="toggleFavorite()" class="tag-clickable">
            <fa-icon size="lg" v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" class="clickable text-primary" style="font-size:1.2rem" />
            <fa-icon size="lg" v-else fixed-width :icon="['far', 'star']" class="clickable text-secondary" style="font-size:1.2rem" />
          </span>
        </li>
      </ul>
    </div>

    <div class="like-count" :key="likeCount">
      <template v-if="likeCount===1"><span class="mr-2">{{ likeCount }} like</span></template>
      <template v-if="likeCount > 1"><span class="mr-2">{{ likeCount }} likes</span></template>
      <template v-if="commentCount===1"><span class="mr-2">{{ commentCount }} comment</span></template>
      <template v-if="commentCount > 1"><span class="mr-2">{{ commentCount }} comments</span></template>
    </div>

    <b-collapse v-model="renderComments">
      <CommentList
        :post-id="post.id"
        :loading="loadingComments"
        @input="addComment"
        v-model="comments"
      />
    </b-collapse>

  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
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
    likeCount: 0,
    isLikedByMe: false,
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
  },

  data: () => ({
    comments: [], // %NOTE: rendered comments are loaded dynamically as they contain additional relation data,
    renderComments: false,
    isFavoritedByMe: false,
    loadingComments: false,
    commentCount: 0,
    // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() { 
    this.isFavoritedByMe = this.post.stats?.isFavoritedByMe || false
    this.commentCount = this.post.stats.commentCount || 0
  },

  created() {},

  methods: {
    ...Vuex.mapMutations('posts', [ 'UPDATE_PUBLIC_POST' ]),
    toggleLike() { // for Post
      this.$emit('toggleLike')
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

    addComment(comments) {
      this.comments = [ ...comments ]
      this.commentCount = this.commentCount + 1
      const updatedPost = {
        ...this.post,
        stats: {
          ...this.post.stats,
          commentCount: this.commentCount,
        }
      }
      this.UPDATE_PUBLIC_POST({ post: updatedPost })
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
      const updatedPost = {
        ...this.post,
        stats: {
          ...this.post.stats,
          isFavoritedByMe: this.isFavoritedByMe,
        }
      }
      this.UPDATE_PUBLIC_POST({ post: updatedPost })
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
        this.commentCount = value.length
      }
    }
  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

li {
  font-size: 14px;
}

.like-count {
  font-size: 0.80rem;
}

.panel-footer {
  user-select: none;
  margin: 0 -0.4em;
}

.comments-box-bg {
  position: absolute;
  top: calc(100% - 100vh + 130px);
  left: 0;
  width: 100%;
  height: calc(100vh - 130px - 100%);
  background: rgba(0, 0, 0, 0.01);
}

@media (max-width: 576px) {
  .comments-box-bg {
    top: calc(100% - 100vh + 110px);
    height: calc(100vh - 110px - 100%);
  }
}
</style>
