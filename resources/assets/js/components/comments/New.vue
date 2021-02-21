<template>
  <b-form @submit="submit">
    <b-form-input
      class="new-comment"
      v-model="description"
      placeholder="Write a comment...press enter to post"
    />
  </b-form>
</template>

<script>
export default {
  props: {
    resource: { type: String, default: null },
    user: { type: String, default: null },
    parent: { type: String, default: null },
  },
  data: () => ({
    commentable_id: null,
    user_id: null,
    parent_id: null,
    description: '',
  }),
  methods: {
    submit(e) {
      e.preventDefault()
      this.$emit('submit', {
        commentable_id: this.commentable_id,
        user_id: this.user_id,
        parent_id: this.parent_id,
        description: this.description,
      })
      this.reset()
    },
    reset() {
      this.commentable_id = this.resource || null
      this.user_id = this.user || null
      this.parent_id =  this.parent || null
      this.description = ''
    }
  },
  mounted() {
    this.reset()
  },
}
</script>

<style lang="scss" scoped>

</style>
