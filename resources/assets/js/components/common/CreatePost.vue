<template>
  <div class="create_post-crate tag-crate">

    <!-- %FILE: resources/assets/js/components/timelines/CreatePost.vue -->
    <section class="row">
      <div class="col">

        <b-card header-tag="header" footer-tag="footer">
          <template #header>
            <section class="d-flex">
              <div class="my-auto mr-3">
                <h6 class="mb-0">Create a Post</h6>
              </div>
              <!-- <div class="post_create-ctrl d-flex flex-grow-1">
                <b-form-select id="post-type" class="w-auto ml-auto" v-model="postType" :options="ptypes" required />
              </div> -->
            </section>
          </template>
          <div>
            <div class="alert alert-secondary py-1 px-2" role="alert" v-if="scheduled_at" @click="showSchedulePicker()">
              <fa-icon size="lg" :icon="['far', 'calendar-check']" class="text-primary mr-1" />
              <span>Scheduled for</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="closeSchedulePicker">
                <span aria-hidden="true">&times;</span>
              </button>
              <strong class="float-right mr-3">{{ moment.utc(scheduled_at).local().format('MMM DD, h:mm a') }}</strong>
            </div>
            <div class="alert alert-secondary py-1 px-2" role="alert" v-if="expirationPeriod">
              <fa-icon size="lg" :icon="['far', 'hourglass-half']" class="text-primary mr-1" />
              Post will expire in <strong>{{ expirationPeriod > 1 ? `${expirationPeriod} days` : `1 day` }}</strong>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="expirationPeriod=null">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div v-if="postType === 'price'" class="w-100 d-flex pt-3 pl-3 price-select-container">
              <PriceSelector
                class="mr-5"
                :label="$t('priceForFollowers')"
                v-model="price"
              />
              <PriceSelector
                :label="$t('priceForSubscribers')"
                v-model="priceForPaidSubscribers"
              />
              <hr />
            </div>
            <vue-dropzone
              ref="myVueDropzone"
              id="dropzone"
              :options="dropzoneOptions"
              :include-styling=true
              :useCustomSlot=true
              :duplicateCheck=true
              @vdropzone-file-added="onDropzoneAdded"
              @vdropzone-removed-file="onDropzoneRemoved"
              @vdropzone-sending="onDropzoneSending"
              @vdropzone-success="onDropzoneSuccess"
              @vdropzone-error="onDropzoneError"
              @vdropzone-queue-complete="onDropzoneQueueComplete"
              @vdropzone-total-upload-progress="onDropzoneTotalUploadProgress"
              class="dropzone"
            >
              <div class="dz-custom-content">
                <textarea v-model="description" rows="8" class="w-100 p-3"></textarea>
              </div>
              <template v-if="selectedMediafiles && selectedMediafiles.length > 0">
                <UploadMediaPreview
                  :mediafiles="selectedMediafiles"
                  @change="changeMediafiles"
                  @openFileUpload="openDropzone"
                  @remove="removeMediafileByIndex"
                />
              </template>
            </vue-dropzone>
            <AudioRecorder
              v-if="showAudioRec"
              @close="showAudioRec=false;selectedMedia=null"
              @complete="audioRecordFinished"
            />

            <b-progress class="progress-widget" v-if="isBusy" :value="uploadProgress" max="100" animated />
          </div>

          <template #footer>

            <b-row v-if="isTagFormVisible" class="mb-1">
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
                <div class="ml-2" v-b-tooltip.hover.html="{title: 'Enter tags in post body, use hash at start for <em>#publictag</em> or hash and exclamation at end for <em>#privatetag!</em>' }">
                  <fa-icon :icon="['far', 'info-circle']" class="text-secondary" />
                </div>
                <!-- <small>Enter tags in post body, use hash at start for <i>#publictag</i> or hash and exclamation at end for <i>#privatetag!</i></small> -->
              </b-col>
            </b-row>

            <b-row>

              <b-col cols="12" md="8" class="post-create-footer-ctrl d-flex">
                <ul class="list-inline d-flex mb-0 OFF-border-right pt-1">
                  <li id="clickme_to-select" class="selectable select-pic">
                    <fa-icon :icon="selectedMedia==='pic' ? ['fas', 'image'] : ['far', 'image']" size="lg" :class="selectedMedia==='pic' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-if="!isIOS9PlusAndAndroid" @click="recordVideo()" class="selectable select-video">
                    <fa-icon :icon="selectedMedia==='video' ? ['fas', 'video'] : ['far', 'video']" size="lg" :class="selectedMedia==='video' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="recordAudio()" class="selectable select-audio">
                    <fa-icon :icon="selectedMedia==='audio' ? ['fas', 'microphone'] : ['far', 'microphone']" size="lg" :class="selectedMedia==='audio' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="renderVaultSelector()" class="selectable">
                    <fa-icon :icon="selectedMedia==='vault' ? ['fas', 'archive'] : ['far', 'archive']" size="lg" :class="selectedMedia==='vault' ? 'text-primary' : 'text-secondary'" />
                  </li>
                </ul>
                <ul class="list-inline d-flex mb-0 pt-1">
                  <!--
                  <li class="selectable select-location"><span><LocationPinIcon /></span> </li>
                  <li class="selectable select-emoji"><span><EmojiIcon /></span></li>
                  <li class="selectable select-timer"><span><TimerIcon /></span></li>
                  <li class="selectable select-calendar" @click="showSchedulePicker()"><span><CalendarIcon /></span></li>
                  -->
                  <li class="selectable select-expire-date" :disabled="expirationPeriod" @click="showExpirationPicker()">
                    <fa-icon :icon="showedModal === 'expiration' ? ['fas', 'hourglass-half'] : ['far', 'hourglass-half']" size="lg" :class="showedModal === 'expiration' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li class="selectable select-calendar" :disabled="scheduled_at" @click="showSchedulePicker()">
                    <fa-icon :icon="showedModal === 'schedule' ? ['fas', 'calendar-alt'] : ['far', 'calendar-alt']" size="lg" :class="showedModal === 'schedule' ? 'text-primary' : 'text-secondary'" />
                  </li>
                </ul>
                <ul class="list-inline d-flex mb-0 pt-1">
                  <li @click="togglePostPrice()" class="selectable select-pic" title="Set Price">
                    <fa-icon :icon="postType === 'price' ? ['fas', 'tag'] : ['far', 'tag']" size="lg" :class="postType === 'price' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="showTagForm()" class="selectable show-tagform" title="Add Tags">
                    <fa-icon :icon="isTagFormVisible ? ['fas', 'hashtag'] : ['far', 'hashtag']" class="text-secondary" size="lg" />
                  </li>
                </ul>
              </b-col>
              <b-col cols="12" md="4" class="pr-0">
                <ul class="list-inline d-flex justify-content-end mb-0 mt-3 mt-md-0">
                  <li class="mx-0">
                    <button @click="onHide && onHide()" v-if="data" class="btn btn-submit btn-secondary">
                      Cancel
                    </button>
                  </li>
                  <li class="w-100 mx-0">
                    <button :disabled="isSaveButtonDisabled" @click="savePost()" class="btn btn-submit btn-primary w-100">
                      <span v-if="isBusy" class="text-white spinner-border spinner-border-sm pr-2" role="status" aria-hidden="true"></span>
                      Post
                    </button>
                  </li>
                </ul>
              </b-col>

            </b-row>

            <p v-if="formErr!==null" class="text-danger">An error occured: {{ formErr }} </p>

          </template>

        </b-card>
      </div>
    </section>

    <VideoRecorder v-if="showVideoRec" @close="showVideoRec=false; selectedMedia=null" @complete="videoRecCompleted" />

  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'
import { isAndroid, isIOS, osVersion } from 'mobile-device-detect'
import heic2any from 'heic2any'

import { eventBus } from '@/eventBus'
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import EmojiIcon from '@components/common/icons/EmojiIcon.vue'
import LocationPinIcon from '@components/common/icons/LocationPinIcon.vue'
import TimerIcon from '@components/common/icons/TimerIcon.vue'
import CalendarIcon from '@components/common/icons/CalendarIcon.vue'

import PriceSelector from '@components/common/PriceSelector'
import UploadMediaPreview from '@components/posts/UploadMediaPreview'
import VideoRecorder from '@components/videoRecorder'
import AudioRecorder from '@components/audioRecorder'

export default {

  comments: true, // %FIXME

  props: {
    session_user: null,
    timeline: null,
    data: null,
    onHide: { type: Function },
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

    isIOS9PlusAndAndroid() {
      return (isIOS && parseInt(osVersion.split('.')[0]) >= 9) || isAndroid
    },

    ...Vuex.mapState('vault', [
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),

    // ref:
    //  ~ https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/SendAdditionalParamsDemo.vue
    //  ~ https://www.dropzonejs.com/#config-autoProcessQueue
    dropzoneOptions() {
      return {
        url: this.$apiRoute('mediafiles.store'),
        paramName: 'mediafile',
        maxFiles: null,
        autoProcessQueue: false,
        thumbnailWidth: 100,
        clickable: '#clickme_to-select',
        maxFilesize: 5000, // 5 GB

        // https://stackoverflow.com/questions/46379917/dropzone-js-upload-with-php-failed-after-30-seconds-upload
        timeout: 0, // disables timeout

        addRemoveLinks: true,
        removeType: 'client',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
      }
    },

    isSaveButtonDisabled() {
      const descriptionWithoutAdminTags = this.description.replace(/\B#\w\w+!/g,'').trim()
      return this.isBusy || (!descriptionWithoutAdminTags && ( this.selectedMediafiles && this.selectedMediafiles.length===0 ))
    },


  }, // computed

  data: () => ({
    moment,
    newPostId: null,
    description: '',
    selectedMedia: null, // 'pic',
    showedModal: null,
    postType: 'free',
    ptypes: [
      { text: 'Free for Followers', value: 'free' },
      { text: 'For Purchase Only', value: 'price' },
      { text: 'For Subscribers', value: 'paid' },
    ],
    price: 0,
    priceForPaidSubscribers: 0,
    currency: 'USD',

    scheduled_at: null,
    //mediafiles: [],  use selectedMediafiles from store
    isBusy: false, // disable button, form, etc due to post sending or some similar activity
    expirationPeriod: null,
    showVideoRec: false,
    showAudioRec: false,

    isTagFormVisible: false,
    isPublicTagFormSelected: true,

    formErr: null, // null if no error, otherwise string (error message)

    uploadProgress: 0,
    uploadFailedFilesCount: 0,
    uploadingFilesCount: 0,
  }), // data

  methods: {

    ...Vuex.mapMutations('vault', [
      'ADD_SELECTED_MEDIAFILES',
      'CLEAR_SELECTED_MEDIAFILES',
      'UPDATE_SELECTED_MEDIAFILES',
      'REMOVE_SELECTED_MEDIAFILE_BY_INDEX',
    ]),

    ...Vuex.mapActions('vault', [
      'getUploadsVaultFolder',
    ]),

    resetForm() {
      this.CLEAR_SELECTED_MEDIAFILES()
      this.$refs.myVueDropzone.removeAllFiles()
      this.description = ''
      this.newPostId = null
      this.selectedMedia = 'pic'
      this.ptype = 'free'
      this.price = 0
      this.priceForPaidSubscribers = 0
      this.scheduled_at = null
      this.expirationPeriod = null
    },

    parseHashtags(searchText) {
      //const regexp = /\B\#\w\w+\b/g
      const regexp = /\B#\w\w+(!)?/g
      const htList = searchText.match(regexp) || [];
      return htList.map(s => s.slice(1))
      // "#baz! #foo! #cat #bar!".match(/\B#\w\w+!\B/g) => [ "#baz!", "#foo!", "#bar!" ]
      // "#baz! #foo! #cat #bar!".match(/\B#\w\w+\b/g) => [ "#baz", "#foo", "#cat", "#bar" ]
    },

    isHashtagPrivate(s) {
      return s.endsWith('!')
    },

    async savePost() {
      this.formErr = null // clear errors
      this.isBusy = true
      console.log('CreatePost::savePost()')
      // (1) create the post
      let response = null
      const payload = {
          timeline_id: this.timeline.id,
          description: this.description,
          type: this.postType,
          price: this.price,
          price_for_subscribers: this.priceForPaidSubscribers,
          currency: this.currency,
          schedule_datetime: this.scheduled_at?.toDate(),
          expiration_period: this.expirationPeriod,
      }

      try { 
        response = await axios.post( this.$apiRoute('posts.store'), payload )
      } catch (e) {
        //console.log('err', { e, })
        if ( e.response?.status === 422 ) {
          const firstKey = Object.keys(e.response.data.errors)[0]
          const msg = e.response.data.errors[firstKey][0]
          this.formErr = `Save failed, check input for errors: ${msg}`
        } else {
          this.formErr = `Save post failed, please try again (${e.message})`
        }
        this.isBusy = false
        return
      }
      this.$log.debug('savePost', { response })
      const json = response.data

      // (2) upload & attach the mediafiles (in dropzone queue)
      if (json.post) {
        this.newPostId = json.post.id
        const queued = this.$refs.myVueDropzone.getQueuedFiles()

        // %FIXME: if this fails, don't we have an orphaned post (?)
        // %NOTE: files added manually don't seem to be put into the queue, thus onDropzoneSending won't be called for them (?)

        if (queued.length) {
          this.uploadingFilesCount = queued.length
          console.log('CreatePost::savePost() - process queue', { queued, })
          this.$refs.myVueDropzone.processQueue() // this will call createCompleted() via callback
        }  else {
          console.log('CreatePost::savePost() - nothing queued')
          this.createCompleted()
        }

      } else {
        this.resetForm()
        this.formErr = null // clear errors
        this.isBusy = false
      }

      if (this.onHide) {
        this.onHide()
      }
    },

    // ------------ Dropzone ------------------------------------------------ //

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click()
    },

    async onDropzoneAdded(file) {
      this.$log.debug('onDropzoneAdded', {file})
      let payload = { ...file, type: file.type }
      if (!file.filepath) {
        if (file.type == 'image/heic' || file.type == 'image/heif') {
          const url = await heic2any({ blob: file })
            .then((conversionResult) => {
              let newFile = new File([conversionResult], file.name.replace(/.hei[c,f]/i, '.jpg'), {type:"image/jpeg", lastModified:new Date().getTime()});
              this.$refs.myVueDropzone.addFile(newFile)
              return URL.createObjectURL(conversionResult)
            })
          payload.filepath = url
          payload.type = "image/jpeg"
        } else {
          payload.filepath = URL.createObjectURL(file)
        }
      }
      this.ADD_SELECTED_MEDIAFILES(payload)
      if (file.type == 'image/heic' || file.type == 'image/heif') {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
      this.$nextTick(() => this.$forceUpdate())
    },

    // Dropzone: 'Modify the request and add addtional parameters to request before sending'
    onDropzoneSending(file, xhr, formData) {
      // %NOTE: file.name is the mediafile PKID
      this.$log.debug('onDropzoneSending', { file, formData, xhr })
      if ( !this.newPostId ) {
        throw new Error('Cancel upload, invalid post id')
      }
      formData.append('resource_id', this.newPostId)
      formData.append('resource_type', 'posts')
      formData.append('mftype', 'post')
    },

    onDropzoneTotalUploadProgress(totalUploadProgress, totalBytes, totalBytesSent) {
      this.uploadProgress = totalUploadProgress
    },

    onDropzoneSuccess(file, response) {
      this.$log.debug('onDropzoneSuccess', { file, response })
      // Remove Preview
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
      // Add Mediafile reference
      //this.ADD_SELECTED_MEDIAFILES(response.mediafile) // we don't need to do this as file is already uploaded & associated with post
    },

    onDropzoneError(file, message, xhr) {
      this.$log.error('Dropzone Error Event', { file, message, xhr })
      if (file) {
        this.uploadFailedFilesCount += 1;
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
    },

    onDropzoneQueueComplete() {
      // Retrieves the newly created post to display at top of feed
      // Not sure why but this event is invoked when image add fails (eg, drag & drop to dropzone fails), so protect against it
      if ( !this.newPostId ) {
        return
      }
      this.createCompleted()
    },

    onDropzoneRemoved(file, error, xhr) {
      this.$log.debug('onDropzoneRemoved')
      //const index = _.findIndex(this.selectedMediafiles, mf => {
      //  return mf.filepath === file.filepath
      //})
      //this.removeMediafileByIndex(index)
    },

    removeFileFromSelected(file) {
      this.$log.debug('removeFileFromSelected')
      const index = _.findIndex(this.selectedMediafiles, mf => {
        return mf.upload ? mf.upload.filename === file.name : false
      })
      this.removeMediafileByIndex(index)
    },

    // %NOTE: this can be called as a handler for the 'remove' event emitted by UploadMediaPreview
    removeMediafileByIndex(index) {
      console.log('CreatePost::removeMediafileByIndex()', {
        index,
      })
      if (index > -1)  {

        // If the file is in the Dropzone queue remove it from there as well
        let dzUUID = null
        if ( typeof this.selectedMediafiles[index] !== 'undefined' ) {
          const file = this.selectedMediafiles[index]
          if ( file.hasOwnProperty('upload') ) {
            dzUUID = file.upload.uuid
          }
        }

        if ( dzUUID !== null ) {
          // workaround...so we can also remove from Dropzone if its a disk file...
          this.$refs.myVueDropzone.getQueuedFiles().forEach( qf => {
            if ( qf.hasOwnProperty('upload') && qf.upload.uuid === dzUUID ) {
              this.$refs.myVueDropzone.removeFile(qf)
            }
          })
        }

        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

    // ---

    async createCompleted() {
      if (this.uploadFailedFilesCount > 0 && this.uploadingFilesCount === this.uploadFailedFilesCount) {
        this.$root.$bvToast.toast('Uploading files failed.', {
          title: 'Warning!',
          variant: 'danger',
          solid: true,
        })
        await axios.delete(`/posts/${this.newPostId}`)
        this.resetForm()
        this.isBusy = false
      }
      // Take care of any files attached from vault (disk files have already been removed from selectedMediafiles)...
      this.selectedMediafiles.forEach( async mf => {
        await axios.post(this.$apiRoute('mediafiles.store'), {
          mediafile_id: mf.id, // the presence of this field is what tells controller method to create a reference, not upload content
          resource_id: this.newPostId,
          resource_type: 'posts',
          mftype: 'post',
        })
      })
      // %TODO: find medaifile in selectedMediafiles and remove it

      this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId })
      this.$store.dispatch('getQueueMetadata')
      // Show notification if scheduled post is succesfully created
      if (this.scheduled_at) {
        this.$root.$bvToast.toast('New scheduled post is created.', {
          title: 'Success!',
          variant: 'primary',
          solid: true,
        })
      }
      this.resetForm()
      this.isBusy = false
    },

    takePicture() { // %TODO
      this.selectedMedia = this.selectedMedia!=='pic' ? 'pic' : null
    },
    recordVideo() { // %TODO
      this.selectedMedia = this.selectedMedia!=='video' ? 'video' : null
      this.showVideoRec = true
    },
    recordAudio() { // %TODO
      this.selectedMedia = this.selectedMedia!=='audio' ? 'audio' : null
      this.showAudioRec = true
    },

    renderVaultSelector() {
      eventBus.$emit('open-modal', {
        key: 'render-vault-selector',
        data: { 
          context: 'create-post',
        },
      })
    },

    showSchedulePicker() {
      this.showedModal = 'schedule'
      eventBus.$emit('open-modal', {
        key: 'show-schedule-datetime',
        data: {
          scheduled_at: this.scheduled_at,
        }
      })
    },

    showTagForm() {
      this.isTagFormVisible = !this.isTagFormVisible
    },

    changeMediafiles(data) {
      this.UPDATE_SELECTED_MEDIAFILES([...data])
    },

    showExpirationPicker() {
      this.showedModal = 'expiration'
      eventBus.$emit('open-modal', {
        key: 'expiration-period',
      })
    },

    closeSchedulePicker(e) {
      this.scheduled_at = null
      e.stopPropagation()
    },

    audioRecordFinished(file) {
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file)
      }
    },

    showCampaignModal() {
      this.showedModal = 'campaign'
      eventBus.$emit('open-modal', {
        key: 'modal-promotion-campaign',
      })
    },

    videoRecCompleted(file) {
      this.showVideoRec = false
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file)
      }
    },

    togglePostPrice() {
      if (this.postType === 'free') {
        this.postType = 'price'
      } else {
        this.postType = 'free'
      }
    }
  },

  mounted() {

    const self = this
    eventBus.$on('apply-schedule', function(data) {
      self.scheduled_at = data
    })
    eventBus.$on('set-expiration-period', function(data) {
      self.expirationPeriod = data
    })

    const params = this.data || this.$route.params

   if ( params.context ) {
     switch( params.context ) {
       case 'send-selected-mediafiles-to-post': // we got here from the vault, likely with mediafiles to attach to a new post
         const mediafileIds = params.mediafile_ids || []
         if ( mediafileIds.length ) {
           // Retrieve any 'pre-loaded' mediafiles, and add to dropzone...be sure to tag as 'ref-only' or something
           const response = axios.get(this.$apiRoute('mediafiles.index'), {
             params: {
               mediafile_ids: mediafileIds,
             },
           }).then( response => {
             response.data.data.forEach( mf => {
               this.ADD_SELECTED_MEDIAFILES(mf)
             })
           })
         }
         break
     } // switch
   }

  }, // mounted

  created() {
    this.dropzoneConfigs = {
      pic: {
        availFileTypes: 'image/*', // csv format
        maxFiles: 10,
      },
      video: {
        availFileTypes: 'video/*', // csv format
        maxFiles: 1,
      },
      audio: {
        availFileTypes: 'audio/*', // csv format
        maxFiles: 1,
      },
    }
    this.dropzoneOptions.maxFiles = this.dropzoneConfigs.pic.maxFiles
    eventBus.$on('close-modal', () => {
      this.showedModal = null
    })
  },

  beforeDestroy() {
    // Clear out any mediafiles so they don't get "carried" between threads before send is clicked
    this.CLEAR_SELECTED_MEDIAFILES()
    this.$refs.myVueDropzone.removeAllFiles()
  },

  watch: {

    hashtags(newVal, oldVal) {
      this.isTagFormVisible = this.hashtags.length > 0
    },

    description(newVal, oldVal) {
      if (newVal!==oldVal) {
        this.formErr = null // clear errors
      }
    },
    //this.$log.debug('watch selectedMediafiles', { value })

  }, // watch

  components: {
    PriceSelector,
    vueDropzone: vue2Dropzone,
    EmojiIcon, LocationPinIcon, TimerIcon, CalendarIcon,
    UploadMediaPreview,
    VideoRecorder,
    AudioRecorder,
  },

}
</script>

<style lang="scss" scoped>

.card-header .dropdown {
}

.card-body {
  padding: 0 !important;
}

.create_post-crate textarea,
.create_post-crate .dropzone,
.create_post-crate .vue-dropzone {
  border: none;
}

li.selectable {
  font-size: 14px;
  cursor: pointer;
}

li.selectable[disabled] {
  cursor: default;
  pointer-events: none;
  opacity: 0.6;
}

.create_post-crate .dropzone.dz-started .dz-message {
  display: block;
}
.create_post-crate .dropzone {
  padding: 0;
}

.create_post-crate .dropzone .dz-message {
  width: 100%;
  text-align: center;
  margin: 0 !important;
}

.create_post-crate textarea {
  resize: none;
  @media (max-width: 576px) {
    height: 125px;
  }
}

.create_post-crate ::v-deep.swiper-slider {
  padding: 0.5rem 1rem !important;
}

.create_post-crate footer ul li {
  padding-left: 0.5em;
  padding-right: 0.5em;
}
.create_post-crate footer ul li span {
  color: #859AB5;
  font-size: 18px;
}
.create_post-crate footer .btn {
  padding: 6px 18px;
}
.create_post-crate header h6 {
  color: #5B6B81;
}

.b-icon.bi {
  vertical-align: middle;
}

.post-create-footer-ctrl {
  font-size: 1.5rem;
}

#post-type {
  font-size: 14px;
}

.price-select-container {
  border-bottom: 1px solid rgba(0,0,0,.125)
}

.progress-widget {
  margin: 0.5rem 1rem !important;
}
</style>

<i18n lang="json5" scoped>
  {
    "en": {
      "priceForFollowers": "Price for free followers",
      "priceForSubscribers": "Price for paid subscribers",
    }
  }
  </i18n>
