<template>
  <div class="container p-0">
    <b-card header-tag="header" footer-tag="footer" class="position-relative">
      <template #header>
        <section class="d-flex report-post-header">
          <div class="my-auto mr-3">
            <div class="h5 mb-0" v-text="$t('title')" />
          </div>
          <div class="post_create-ctrl d-flex align-items-center flex-grow-1">
            <button type="button" @click="discard" aria-label="Close" class="close ml-auto">×</button>
          </div>
        </section>
      </template>
      <LoadingOverlay :loading="loading" />
      <div>
        <textarea v-model="reason" rows="8" class="w-100" placeholder="Please tell us why you are reporting this post..."></textarea>
      </div>
      <template #footer>
        <b-row>
          <b-col cols="12" class="d-flex justify-content-end">
            <b-btn class="px-3" variant="primary" :disabled="!isSaveable" @click="save">{{ $t('save.button') }}</b-btn>
          </b-col>
        </b-row>
      </template>
    </b-card>
  </div>
</template>
<script>
import { eventBus } from '@/eventBus'
import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: "ReportPost",

  props: {
    post: { type: Object, default: () => ({}) },
  },

  computed: {
    isSaveable() {
      return (this.loading) ? false  : (this.reason !== '')
    }
  },

  data: () => ({
    loading: false,
    reason: '',
  }),

  methods: {

    save(e) {
      this.loading = true
      this.axios.post(this.$apiRoute('contentflags.store'), {
        //flagger_id: this.flagger_id,
        flaggable_id: this.post.id,
        flaggable_type: 'posts',
        notes: this.reason,
      }).then(response => {
        this.loading = false
        //eventBus.$emit('update-posts', this.post.id)
        this.exit()
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('save.error') })
        this.loading = false
      })
    },

    discard(e) {
      this.exit()
    },

    exit() {
      this.$bvModal.hide('report-post');
    },

  },

  mounted() { },

  components: {
    LoadingOverlay,
  },

}
</script>
<style lang="scss" scoped>
textarea,
.dropzone,
.vue-dropzone {
  border: none;
}
.select-calendar {
  cursor: pointer;
  align-self: center;
}
.report-post-header button.close {
  padding: 1rem;
  margin: -1rem -1rem -1rem auto;
  line-height: 1em;
}
</style>
<i18n lang="json5" scoped>
{
  "en": {
    "title": "Report Post",
    "loading": {
      "error": "An error has occurred while attempting to load this post. Please return to the previous page and try again later."
    },
    "save": {
      "button": "Report",
      "error": "An error has occurred while attempting to report this post. Please try again later."
    },
    "priceForFollowers": "Price for free followers",
    "priceForSubscribers": "Price for paid subscribers",
  }
}
</i18n>
