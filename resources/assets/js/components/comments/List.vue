<template>
  <section class="post-comments mt-3">
    <New @submit="submitComment" class="mb-3" />
    <ul class="list-basecomments list-unstyled mt-1">
      <li v-for="(comment, index) in value" :key="comment.id" class="mb-3">
        <Display
          :comment="comment"
          :session_user="session_user"
          :post-id="postId"
          @updated="(v) => updateComment(v, index)"
        />
      </li>
      <li v-if="loading">
        <div class="w-100 text-center">
          <fa-icon icon="spinner" spin size="lg" />
        </div>
      </li>
    </ul>
  </section>
</template>

<script>
/**
 * List View for comments
 */
import _ from 'lodash'
import Display from './Display'
import New from './New'
import Vuex from 'vuex'

export default {
  components: {
    Display,
    New,
  },
  props: {
    value: { type: Array, default: () => ([])},
    loading: { type: Boolean, default: false},
    postId: { type: String, default: null },
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),
  },

  methods: {
    addComment(comment) {
      this.$emit('input', [ ...this.value, comment ])
    },
    updateComment(v, index) {
      var newValue = _.cloneDeep(this.value)
      newValue.splice(index, 1, v)
      this.$emit('input', newValue)
    },

    async submitComment(form) {
      form.post_id = this.postId
      form.user_id = this.session_user.id
      form.parent_id = null // %TODO

      // Add comment to list
      const response = await this.axios.post(this.$apiRoute('comments.store'), form, { headers: { 'Content-Type': 'application/json' } } )
      this.addComment(response.data.comment)
    },
  },

}
</script>
