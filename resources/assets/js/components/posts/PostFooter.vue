<template>
        <div class="panel-footer fans">
          <ul class="list-inline footer-ctrl mb-0">
            <li class="list-inline-item mr-3">
              <LikesButton @toggled="togglePostLike()" :filled="isLikedByMe" :count="likeCount" :showCount="true" />
            </li>
            <li class="list-inline-item mr-3">
              <span @click="toggleComments()" class="tag-clickable">
                <b-icon icon="chat-text" font-scale="1" />
                ({{ post.stats.commentCount }})
              </span>
            </li>
            <li class="list-inline-item mr-3">
              <span @click="share()" class="tag-clickable">
                <b-icon icon="share" font-scale="1" />
              </span>
            </li>
            <li class="list-inline-item mr-3">
              <span @click="tip()" class="tag-clickable">$</span>
            </li>
            <li class="list-inline-item mr-3">
              <span @click="addBookmark()" class="tag-clickable">
                <b-icon icon="bookmark" font-scale="1" />
              </span>
            </li>
          </ul>

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
//import { eventBus } from '@/app'
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
    loadingComments: false,
    // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() { 
    this.isLikedByMe = this.post.stats?.isLikedByMe || false
    this.likeCount = this.post.stats?.likeCount  || 0
  },

  created() {},

  methods: {
    async togglePostLike() {
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

    tip() {},

    addComment(comment) {
      this.comments = [ ...this.comments, comment ]
      this.post.comments_count = this.post.comments_count + 1
    },

    addBookmark(bookmark) {
    },

    updateCommentsCount(value, index) {
      this.comments = this.comments.splice(index, 1, value)
    },

    toggleComments() {
      const isCurrentlyVisible = !!this.renderComments
      if (isCurrentlyVisible) {
        this.renderComments = false // toggle -> hide
      } else {
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
footer.card-footer {
  background-color: #fff;
}

</style>
