<template>
  <div class="container p-0">

    <b-card header-tag="header" footer-tag="footer" class="position-relative">

      <template #header>
        <section class="d-flex edit-post-header">
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
        <div class="alert alert-secondary py-1 px-2" role="alert" v-if="schedule_datetime" @click="showSchedulePicker()">
          <fa-icon size="lg" :icon="['far', 'calendar-check']" class="text-primary mr-1" />
          <span>Scheduled for</span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="closeSchedulePicker">
            <span aria-hidden="true">&times;</span>
          </button>
          <strong class="float-right mr-3">{{ moment.utc(schedule_datetime).local().format('MMM DD, h:mm a') }}</strong>
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

        <!--
        <b-row class="mb-1">
          <b-col cols="12" md="8" class="d-flex">
            Public:
            <b-form-tags input-id="edit-post-contenttags" v-model="contenttags"></b-form-tags>
          </b-col>
        </b-row>
        <b-row class="mb-1">
          <b-col cols="12" md="8" class="d-flex">
            Private:
            <b-form-tags input-id="edit-post-contenttags_mgmt" v-model="contenttags_mgmt"></b-form-tags>
          </b-col>
        </b-row>
        -->
            <b-row v-if="true || isTagFormVisible" class="mb-1">
              <b-col cols="12" class="d-flex align-items-center">
                <b-form-tags v-model="hashtags" no-outer-focus class="">
                  <template v-slot="{ tags, inputAttrs, inputHandlers, tagVariant, addTag, removeTag }">
                    <div class="d-inline-block">
                      <b-form-tag v-for="tag in tags" 
                        @remove="removeTag(tag)" 
                        :key="tag" 
                        :title="tag" 
                        :variant="isHashtagPrivate(tag) ? 'danger' : 'secondary'" 
                        size="sm" class="mr-1" 
                      > 
                        {{ tag }}
                      </b-form-tag>
                    </div>
                  </template>
                </b-form-tags>
                <div class="ml-2" v-b-tooltip.hover.html="{title: 'Enter tags in post body, use hash at start for <em>#publictag</em> or hash and exclamation at end for <em>#privatetag!</em>', variant: 'info'}">
                  <fa-icon :icon="['fas', 'info']" class="text-warning" />
                </div>
                <!-- <small>Enter tags in post body, use hash at start for <i>#publictag</i> or hash and exclamation at end for <i>#privatetag!</i></small> -->
              </b-col>
            </b-row>

        <b-row>
          <b-col cols="4" md="4" class="d-flex">
            <ul class="list-inline d-flex mb-0">
              <li class="selectable select-calendar" v-if="post && post.schedule_datetime" @click="showSchedulePicker()">
                <fa-icon size="lg" :icon="['far', 'calendar-check']" class="text-secondary" />
              </li>
            </ul>
          </b-col>
          <b-col cols="8" md="8" class="d-flex justify-content-end">
            <div class="d-flex">
              <b-btn class="px-3" variant="primary" :disabled="!changed" @click="save">
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
import { eventBus } from '@/eventBus'
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

    hashtags: {
      // tag representation in the create post footer (can be deleted here but not added)
      get: function () {
        return this.parseHashtags(this.description) || []
      },
      set: function (newValue) {
        const oldValue = this.parseHashtags(this.description) || []
        const diffs = oldValue.filter( s => !newValue.includes(s) )
        diffs.forEach( s => {
          console.log(`replacing ${s}`)
          this.description = this.description.replace('#'+s, '')
        })
      }
    },

    changed() {
      if (!this.loading) {
        return this.post.description !== this.description
          || this.post.type !== this.type
          || this.post.price !== this.price
          || this.post.price_for_subscribers !== this.priceForPaidSubscribers
          || this.post.schedule_datetime !== this.schedule_datetime
        //|| this.post.contenttags !== this.contenttags
        //|| this.post.contenttags_mgmt !== this.contenttags_mgmt
      }
      return false
    }
  },

  data: () => ({
    loading: false,
    description: '',
    //contenttags: [],
    //contenttags_mgmt: [],
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

    discard(e) {
      this.$bvModal.hide('edit-post');
    },

    save(e) {
      // tags are parsed out of the post's content body by the backend...so add them in here from the separate 'tag' form
      //   ~ "fix" any tags prefixed with '#' by user (remove the '#')...since we add the '#' ourselves here before sending
      //      That is the intended usage is  for the user to type in tags like "foo" rather than "#foo"
      /*
      const tmp = this.description
      if ( this.contenttags.length > 0 ) {
        this.description += ' #'+this.contenttags.map( ct => ct.replace(/^#/,'') ).join(' #')
      }
      if ( this.contenttags_mgmt.length > 0 ) {
        // %FIXME: need to postfix with '!'
        //this.description += ' #'+this.contenttags_mgmt.map( ct => ct.replace(/^#/,'') ).join(' #')
      }
       */
      const payload = {
        description: this.description,
        type: this.type,
        price: this.price,
        price_for_subscribers: this.priceForPaidSubscribers,
        currency: this.currency,
        schedule_datetime: this.schedule_datetime,
      }
      console.log('save', { 
        payload,
        //tags: this.contenttags,
        //tmp,
        desc: this.description,
      })
      this.loading = true
      this.axios.patch(this.$apiRoute('posts.update', { post: this.post.slug }), payload ).then(response => {
        this.loading = false
        eventBus.$emit('update-posts', this.post.id)
        this.$bvModal.hide('edit-post');
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('save.error') })
        this.loading = false
      })
    },

    showSchedulePicker() {
      eventBus.$emit('open-modal', {
        key: 'show-schedule-datetime',
        data: {
          scheduled_at: this.schedule_datetime,
          is_for_edit: true,
        }
      })
    },

    closeSchedulePicker(e) {
      this.schedule_datetime = null;
      e.stopPropagation();
    },

    parseHashtags(searchText) {
      const regexp = /\B#\w\w+(!)?/g
      const htList = searchText.match(regexp) || [];
      return htList.map(s => s.slice(1))
    },

    isHashtagPrivate(s) {
      return s.endsWith('!')
    },

  },

  created() {
    this.type = this.post.type
    this.price = this.post.price
    this.priceForPaidSubscribers = this.post.price_for_subscribers
    // this.currency = this.post.currency

    if ( this.post.contenttags_mgmt.length > 0 ) {
      const str = this.post.contenttags_mgmt.map( ct => `#${ct}!`).join(' ')
      // embed private tags at end of post for editing (public tags are already in post body as saved in DB)
      this.description = this.post.description + ' ' + this.post.contenttags_mgmt.map( ct => `#${ct}!`).join(' ')
    } else {
      this.description = this.post.description
    }
  },

  mounted() {
    if (this.post.schedule_datetime) {
      this.schedule_datetime = moment.utc(this.post.schedule_datetime)
    }

    const self = this
    eventBus.$on('edit-apply-schedule', function(data) {
      self.schedule_datetime = data
    })
  },

  watch: {

    /*
    hashtags(newVal, oldVal) {
      this.isTagFormVisible = this.hashtags.length > 0
    },

    description(newVal, oldVal) {
      if (newVal!==oldVal) {
        this.formErr = null // clear errors
      }
    },
     */

  }, // watch
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
.edit-post-header button.close {
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
