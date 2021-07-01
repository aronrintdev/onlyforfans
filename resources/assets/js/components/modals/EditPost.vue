
<template>
  <div class="container p-0">
    <b-card header-tag="header" footer-tag="footer" class="position-relative">
      <template #header>
        <section class="d-flex">
          <div class="my-auto mr-3">
            <div class="h5 mb-0" v-text="$t('title')" />
          </div>
          <div class="post_create-ctrl d-flex align-items-center flex-grow-1">
            <b-form-select id="post-type" class="w-auto ml-auto" v-model="type" :options="ptypes" required />
            <button type="button" @click="discard" aria-label="Close" class="close ml-3">×</button>
          </div>
        </section>
      </template>
      <LoadingOverlay :loading="loading" />
      <div>
        <div class="scheduled-message-head" v-if="schedule_datetime">
          <div>
            <svg class="icon-schedule" viewBox="0 0 24 24">
              <path d="M19 3h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V5a2 2 0 0 0-2-2zm0 15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9h14zm0-11H5V5h14zM9.79 17.21a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .29-.71 1 1 0 0 0-1-1 1 1 0 0 0-.71.29l-4.29 4.3-1.29-1.3a1 1 0 0 0-.71-.29 1 1 0 0 0-1 1 1 1 0 0 0 .29.71z"></path>
            </svg> 
            <span> Scheduled for&nbsp;</span>
            <strong>{{ moment(schedule_datetime * 1000).local().format('MMM DD, h:mm a') }}</strong>
          </div>
          <button class="btn close-btn" @click="clearSchedule">
            <svg class="icon-close" viewBox="0 0 24 24">
              <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
            </svg>
          </button>
        </div>
        <div v-if="type === 'price'" class="d-flex w-100">
          <PriceSelector
            class="mb-3 mr-5"
            :label="$t('priceForFollowers')"
            v-model="price"
          />
          <PriceSelector
            class="mb-3"
            :label="$t('priceForSubscribers')"
            v-model="priceForPaidSubscribers"
          />
          <hr />
        </div>
        <textarea v-model="description" rows="8" class="w-100"></textarea>
      </div>
      <template #footer>
        <b-row>
          <b-col cols="4" md="4" class="d-flex">
            <ul class="list-inline d-flex mb-0">
              <li
                class="selectable select-calendar"
                v-if="post && post.schedule_datetime"
                @click="showSchedulePicker()"
              >
                <span><CalendarIcon /></span>
              </li>
            </ul>
          </b-col>
          <b-col cols="8" md="8" class="d-flex justify-content-end">
            <div class="d-flex">
              <b-btn variant="primary" :disabled="!changed" @click="save">
                {{ $t('save.button') }}
              </b-btn>
            </div>
          </b-col>
        </b-row>
      </template>
    </b-card>
  </div>
</template>
<script>
/**
 * js/views/posts/Edit
 *
 * Edit Post View
 */
import moment from 'moment'
import { eventBus } from '@/app'
import PriceSelector from '@components/common/PriceSelector'
import LoadingOverlay from '@components/common/LoadingOverlay'
import CalendarIcon from '@components/common/icons/CalendarIcon.vue'

export default {
  name: "EditPost",
  components: {
    LoadingOverlay,
    PriceSelector,
    CalendarIcon,
  },
  props: {
    post: { type: Object, default: () => ({}) },
  },
  computed: {
    changed() {
      if (!this.loading) {
        return this.post.description !== this.description
          || this.post.type !== this.type
          || this.post.price !== this.price
          || this.post.price_for_subscribers !== this.priceForPaidSubscribers
          || this.post.schedule_datetime !== this.schedule_datetime
      }
      return false
    }
  },
  data: () => ({
    loading: false,
    description: '',
    type: 'free',
    price: 0,
    priceForPaidSubscribers: 0,
    currency: 'USD',
    ptypes: [
      { text: 'Free', value: 'free' },
      { text: 'By Purchase', value: 'price' },
      { text: 'Subscriber-Only', value: 'paid' },
    ],
    postSchedule: {},
    moment,
    schedule_datetime: null,
  }),
  methods: {
    fillFromProp() {
      this.description = this.post.description
      this.type = this.post.type
      this.price = this.post.price
      this.priceForPaidSubscribers = this.post.price_for_subscribers
      // this.currency = this.post.currency
      this.schedule_datetime = this.post.schedule_datetime
    },
    discard(e) {
      this.exit()
    },
    save(e) {
      this.loading = true
      this.axios.patch(this.$apiRoute('posts.update', { post: this.post.slug }), {
        description: this.description,
        type: this.type,
        price: this.price,
        price_for_subscribers: this.priceForPaidSubscribers,
        currency: this.currency,
        schedule_datetime: this.schedule_datetime,
      }).then(response => {
        this.loading = false
        eventBus.$emit('update-posts', this.post.id)
        this.exit()
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('save.error') })
        this.loading = false
      })
    },
    exit() {
      this.$bvModal.hide('edit-post');
    },
    clearSchedule() {
      this.schedule_datetime = undefined
    },
    showSchedulePicker() {
      eventBus.$emit('open-modal', {
        key: 'show-schedule-datetime',
      })
    },
  },
  mounted() {
    this.fillFromProp()
    const self = this
    eventBus.$on('apply-schedule', function(data) {
      self.schedule_datetime = data
    })
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
  font-size: 18px;
  cursor: pointer;
}
button.close {
  padding: 1rem;
  margin: -1rem -1rem -1rem auto;
  line-height: 1em;
}
</style>
<i18n lang="json5" scoped>
{
  "en": {
    "title": "Edit Post",
    "loading": {
      "error": "An error has occurred while attempting to load this post. Please return to the previous page and try again later."
    },
    "save": {
      "button": "Save",
      "error": "An error has occurred while attempting to save this post. Please try again later."
    },
    "priceForFollowers": "Price for free followers",
    "priceForSubscribers": "Price for paid subscribers",
  }
}
</i18n>