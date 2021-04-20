<template>
  <div class="container">
    <b-card header-tag="header" footer-tag="footer" class="position-relative">
      <template #header>
        <section class="d-flex">
          <div class="my-auto mr-3">
            <div class="h6 mb-0" v-text="$t('title')" />
          </div>
          <div class="post_create-ctrl d-flex flex-grow-1">
            <b-form-select id="post-type" class="w-auto ml-auto" v-model="type" :options="ptypes" required />
          </div>
        </section>
      </template>

      <LoadingOverlay :loading="loading" />

      <div>
        <div v-if="type === 'price'" class="w-100">
          <PriceSelector v-if="type === 'price'" v-model="price" class="mb-3" />
          <hr />
        </div>

        <textarea v-model="description" rows="8" class="w-100"></textarea>
      </div>

      <template #footer>
        <div class="d-flex">
          <b-btn variant="secondary" @click="discard" class="ml-auto mr-3">
            <fa-icon icon="times" fixed-width class="mr-1" />
            {{ $t('discard.button') }}
          </b-btn>
          <b-btn v-if="changed" variant="secondary" @click="revert" class="mr-3" >
            <fa-icon icon="undo" fixed-width class="mr-1" />
            {{ $t('revert.button') }}
          </b-btn>

          <b-btn variant="success" :disabled="!changed" @click="save">
            <fa-icon icon="save" fixed-width class="mr-1" />
            {{ $t('save.button') }}
          </b-btn>
        </div>
      </template>
    </b-card>
  </div>
</template>

<script>
/**
 * js/views/posts/Edit
 *
 * Edit Post Page
 */
import { eventBus } from '@/app'
import PriceSelector from '@components/common/PriceSelector'
import LoadingOverlay from '@components/common/LoadingOverlay'

export default {
  name: "EditPost",

  components: {
    LoadingOverlay,
    PriceSelector,
  },

  props: {
    slug: { type: String, default: '' },
    post: { type: Object, default: () => ({}) },
  },

  computed: {
    changed() {
      if (!this.loading) {
        return this.rawPost.description !== this.description
          || this.rawPost.type !== this.type
          || this.rawPost.price !== this.price
          // || this.rawPost.currency !== this.currency
      }
      return false
    }
  },

  data: () => ({
    loading: true,

    description: '',
    type: 'free',
    price: 0,
    currency: 'USD',

    ptypes: [
      { text: 'Free', value: 'free' },
      { text: 'By Purchase', value: 'price' },
      { text: 'Subscriber-Only', value: 'paid' },
    ],

    rawPost: {},
  }),

  methods: {
    init() {
      this.axios.get(this.$apiRoute('posts.show', { post: this.slug }))
        .then(response => {
          this.rawPost = response.data.data || response.data.post || response.data
          this.fillFromProp()
          this.loading = false
        })
        .catch(error => eventBus.$emit('error', { error, message: this.$t('loading.error') }))
    },

    fillFromProp() {
      this.description = this.rawPost.description
      this.type = this.rawPost.type
      this.price = this.rawPost.price
      // this.currency = this.rawPost.currency
    },

    discard(e) {
      this.exit()
    },

    revert(e) {
      this.fillFromProp()
    },

    save(e) {
      this.loading = true
      this.axios.patch(this.$apiRoute('posts.update', { post: this.slug }), {
        description: this.description,
        type: this.type,
        price: this.price,
        currency: this.currency
      }).then(response => {
        this.loading = false
        eventBus.$emit('update-post', this.rawPost.id)
        this.exit()
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('save.error') })
        this.loading = false
      })
    },

    exit() {
      this.$router.go(-1)
        || this.$router.push({ name: 'timeline.show', params: { slug: this.rawPost.timeline.slug } })
        || this.$router.push({ name: 'index' })
    }
  },

  mounted() {
    this.init()
    this.fillFromProp()
  },
}
</script>

<style lang="scss" scoped>
textarea,
.dropzone,
.vue-dropzone {
  border: none;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Edit Post",
    "loading": {
      "error": "An error has occurred while attempting to load this post. Please return to the previous page and try again later."
    },
    "discard": {
      "button": "Discard"
    },
    "revert": {
      "button": "Revert"
    },
    "save": {
      "button": "Save",
      "error": "An error has occurred while attempting to save this post. Please try again later."
    }
  }
}
</i18n>