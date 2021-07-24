<template>
  <b-modal :visible="value" @change="v => $emit('input', v)" body-class="p-0" size="xl" hide-header hide-footer>
    <div class="p-relative" style="min-height: 5rem;">
      <div v-if="!loading">
        <Display :post="post" :session_user="session_user" :is_feed="false" displayClose @close="$emit('input', false)" />
      </div>
      <LoadingOverlay :loading="loading" />
      <b-alert :show="error" variant="danger">
        {{ $t('error') }}
      </b-alert>
    </div>
  </b-modal>
</template>

<script>
/**
 * resources/assets/js/components/modals/DisplayPost.vue
 */
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import Display from '@components/posts/Display'
import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: 'DisplayPostModal',

  components: {
    Display,
    LoadingOverlay,
  },

  props: {
    postId: { type: String, default: '' },
    value: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
  },

  data: () => ({
    loading: true,
    post: null,
    error: false,
  }),

  methods: {
    ...Vuex.mapActions('posts', [ 'getPost' ]),

    load() {
      this.loading = true
      this.getPost(this.postId) .then(post => {
        this.post = post
        this.loading = false
      }).catch(error => {
        eventBus.$emit('error', { error, message: $t('error')})
        this.loading = false
        this.error = true
      })
    },
  },

  watch: {},

  created() {
    this.load()
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "error": "An issue has occurred while loading this post."
  }
}
</i18n>
