<template>
  <div class="post-crate" v-bind:data-post_guid="post.id">
    <div>HELLO POST</div>
    <b-card
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-post"
      header-class="d-flex justify-content-between"
    >
      <template #header>
        <div class="post-author">
          <section class="user-avatar">
            <router-link :to="timelineRoute">
              <b-img
                :src="post.user.avatar.filepath"
                :alt="post.user.name"
                :title="post.user.name"
              ></b-img>
            </router-link>
          </section>
          <section class="user-details">
            <ul class="list-unstyled">
              <li>
                <router-link
                  :to="timelineRoute"
                  title=""
                  data-toggle="tooltip"
                  data-placement="top"
                  class="username"
                  v-text="post.user.name"
                />
                <span v-if="post.user.verified" class="verified-badge">
                  <b-icon icon="check-circle-fill" variant="success" font-scale="1" />
                </span>
              </li>
              <li>
                <timeago :datetime="post.created_at" :auto-update="60"></timeago>
                <span v-if="post.location" class="post-place">
                  at
                  <a target="_blank" :href="`/get-location/${post.location}`">
                    <b-icon icon="geo-fill" variant="primary" font-scale="1" />
                    {{ post.location }}
                  </a>
                </span>
              </li>
            </ul>
          </section>
          <div class="tag-debug">
            <ul>
              <li>ID: {{ post.id | niceGuid }}</li>
              <li>Type: {{ post.type | enumPostType }}</li>
              <li>Price: {{ post.price }}</li>
              <li>Access: {{ post.access }}</li>
            </ul>
          </div>
        </div>
        <div v-if="session_user.id === post.user.id" class="post-ctrl">
          <b-dropdown id="dropdown-1" text="" class="m-md-2" variant="outline-dark">
            <b-dropdown-item @click="editPost()">Edit</b-dropdown-item>
            <b-dropdown-item @click="deletePost()">Delete</b-dropdown-item>
          </b-dropdown>
        </div>
      </template>

      <template v-if="post.access">
        <b-card-img v-if="hasMediafiles" :src="primaryMediafile" alt="Image" ></b-card-img>
        <b-card-text> <p>{{ post.description }}</p> </b-card-text>
      </template>
      <template v-else-if="$options.filters.isSubscriberOnly(post)">
        <article class="locked-content d-flex justify-content-center align-items-center">
          <div class="d-flex flex-column">
            <b-icon icon="lock-fill" font-scale="5" variant="light" />
            <b-button @click="renderSubscribe" class="mt-3" variant="primary">Subscribe</b-button>
          </div>
        </article>
      </template>
      <template v-else>
        <article class="locked-content d-flex justify-content-center align-items-center">
          <div class="d-flex flex-column">
            <b-icon icon="lock-fill" font-scale="5" variant="light" />
            <b-button @click="renderPurchasePost" class="mt-3" variant="primary">Buy</b-button>
          </div>
        </article>
      </template>

      <template #footer>
        <div class="panel-footer fans">
          <ul class="list-inline footer-ctrl">
            <li class="list-inline-item mr-3">
              <LikesButton @toggled="togglePostLike()" :filled="isLikedByMe" />
            </li>
            <li class="list-inline-item mr-3">
              <span @click="toggleComments()" class="tag-clickable">
                <b-icon icon="chat-text" font-scale="1" />
                ({{ post.comments_count }})
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
    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import CommentList from '@components/comments/List'
import CommentDisplay from '@components/comments/Display'
import LikesButton from '@components/common/LikesButton'
import NewComment from '@components/comments/New'

export default {
  components: {
    CommentList,
    CommentDisplay,
    LikesButton,
    NewComment,
  },

  props: {
    post: null,
    session_user: null,
  },

  computed: {
    username() {
      return this.post.user.username
    },
    timelineRoute() {
      return {
        name: 'timeline.show',
        params: { slug: this.post.timeline_slug }
      }
    },
    hasMediafiles() {
      return this.post.mediafiles?.length > 0
    },
    primaryMediafile() {
      return this.hasMediafiles ? this.post.mediafiles[0].filepath : null
    },
    isLoading() {
      return !this.post || !this.session_user
    },
  },

  data: () => ({
    renderComments: false,
    isLikedByMe: false, // this.post.isLikedByMe,
    likeCount: 0, // %FIXME INIT
    comments: [], // %NOTE: rendered comments are loaded dynamically as they contain additional relation data,
    loadingComments: false,
    // whereas comment count is computed from the comments relation on the post itself (%FIXME?)
  }),

  mounted() {
    this.isLikedByMe = this.post.isLikedByMe
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

    addComment(comment) {
      this.comments = [ ...this.comments, comment ]
      this.post.comments_count = this.post.comments_count + 1
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

    share() {},

    tip() {},

    editPost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
    },

    renderPurchasePost() {
      //this.$emit('render-purchase-post', this.post)
      //this.$bvModal.show('modal-purchase_post', this.post)
      //eventBus.$emit('render-purchase-post-modal', this.post)
      eventBus.$emit('open-modal', {
        key: 'render-purchase-post', 
        data: {
          post: this.post,
        }
      })
    },

    renderSubscribe() {
      //this.$emit('render-subcribe', this.post)
      //eventBus.$emit('render-subscribe-modal', this.post)
      //this.$bvModal.show('modal-follow', this.post.postable_id) // = timeline id
      eventBus.$emit('open-modal', {
        key: 'render-subscribe', 
        data: {
          //post: this.post,
        }
      })
    },

    deletePost() {
      const is = this.session_user.id === this.post.user.id // Check permissions
      if (!is) {
        return
      }
      this.$emit('delete-post', this.post.id)
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
  }
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
  color: #5b6b81;
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
  color: #859ab5;
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
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}
body .locked-content {
  background: url('/images/locked_post.png') center center no-repeat !important;
  background-size: auto;
  background-size: cover !important;
  min-height: 20rem;
}
</style>
