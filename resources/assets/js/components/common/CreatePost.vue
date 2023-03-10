<template>
  <div class="create_post-crate tag-crate">

    <!-- %FILE: resources/assets/js/components/timelines/CreatePost.vue -->
    <section class="row">
      <div class="col superbox-form">

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
                <div
                  class="text-left text-editor"
                  :contenteditable="!isBusy"
                  v-html="descriptionForEditor"
                  @input="onInput"
                  @click="editorClicked"
                ></div>
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
            <AudioRecorder v-if="showAudioRec" @close="showAudioRec=false; selectedIcon=null" @complete="audioRecordFinished" />

            <b-progress class="progress-widget" v-if="isBusy" :value="uploadProgress" max="100" animated />
          </div>

          <template #footer>
            <b-row v-if="suggestions.length" class="mb-1">
              <b-col cols="12" class="d-flex flex-wrap">
                <div class="bg-secondary mr-2 mb-1 p-1 suggestion" @click="selectSuggestion(suggestion)" v-for="suggestion in suggestions" :key="suggestion.id">
                  {{ suggestion.label }}
                </div>
              </b-col>
            </b-row>
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
                        {{ tag.endsWith('!') ? tag.slice(0, -1) : tag }}
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
                <ul class="list-inline d-flex mb-0 w-100 justify-content-between justify-content-sm-start align-items-center">

                  <li v-b-tooltip.hover="'Add Photo'" id="clickme_to-select" class="selectable select-pic" :disabled="isBusy">
                    <fa-icon :icon="selectedIcon==='pic' ? ['fas', 'image'] : ['far', 'image']" size="lg" :class="selectedIcon==='pic' ? 'text-primary' : 'text-secondary'" />
                  </li>

                  <li v-b-tooltip.hover="'Record Video'" v-if="!isIOS9PlusAndAndroid" @click="recordVideo()" class="selectable select-video" :disabled="isBusy">
                    <fa-icon :icon="selectedIcon==='video' ? ['fas', 'video'] : ['far', 'video']" size="lg" :class="selectedIcon==='video' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-b-tooltip.hover="'Record Audio'" @click="recordAudio()" class="selectable select-audio" :disabled="isBusy">
                    <fa-icon :icon="selectedIcon==='audio' ? ['fas', 'microphone'] : ['far', 'microphone']" size="lg" :class="selectedIcon==='audio' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-b-tooltip.hover="'Add Photo From My Media'" @click="renderVaultSelector()" class="selectable" :disabled="isBusy">
                    <fa-icon :icon="selectedIcon==='vault' ? ['fas', 'photo-video'] : ['far', 'photo-video']" size="lg" :class="selectedIcon==='vault' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-b-tooltip.hover="'Set Expiration Date'" class="selectable select-expire-date" :disabled="expirationPeriod || isBusy" @click="showExpirationPicker()" >
                    <fa-icon :icon="showedModal === 'expiration' ? ['fas', 'hourglass-half'] : ['far', 'hourglass-half']" size="lg" :class="showedModal === 'expiration' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-b-tooltip.hover="'Schedule Publish Date'" class="selectable select-calendar" :disabled="scheduled_at || isBusy" @click="showSchedulePicker()" >
                    <fa-icon :icon="showedModal === 'schedule' ? ['fas', 'calendar-alt'] : ['far', 'calendar-alt']" size="lg" :class="showedModal === 'schedule' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="showTagForm()" class="selectable show-tagform" v-b-tooltip.hover="'Add Tags'" :disabled="isBusy">
                    <fa-icon :icon="isTagFormVisible ? ['fas', 'hashtag'] : ['far', 'hashtag']" :class="isHashtagIconSelected ? 'text-primary' : 'text-secondary'" size="lg" />
                  </li>

                  <li @click="togglePostPrice()" class="selectable select-pic" v-b-tooltip.hover="'Set Post Unlock Price'" :disabled="isBusy">
                    <fa-icon :icon="postType === 'price' ? ['fas', 'tag'] : ['far', 'tag']" size="lg" :class="postType === 'price' ? 'text-primary' : 'text-secondary'" />
                  </li>
                      
                  <li v-custom-click-outside="closeEmojiBox" class="selectable select-emoji" v-b-tooltip.hover="'Add Emoji Icon'" :disabled="isBusy">
                    <div @click="isEmojiBoxVisible=!isEmojiBoxVisible" :disabled="isBusy">
                      <fa-icon :icon="isEmojiBoxVisible ? ['fas', 'smile'] : ['far', 'smile']" :class="isEmojiBoxVisible ? 'text-primary' : 'text-secondary'" size="lg" />
                    </div>
                    <VEmojiPicker v-if="isEmojiBoxVisible" @select="selectEmoji" />
                  </li>
                </ul>
              </b-col>

              <b-col cols="12" md="4" class="px-0">
                <ul class="list-inline d-flex justify-content-end mb-0 mt-3 mt-md-0">
                  <li class="px-0 mx-0">
                    <button @click="onHide && onHide()" v-if="data" class="btn btn-submit btn-secondary" :disabled="isBusy">
                      Cancel
                    </button>
                  </li>
                  <li class="w-100 mx-0">
                    <button :disabled="isSaveButtonDisabled || isBusy" @click="savePost()" class="btn btn-submit btn-primary w-100">
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

    <VideoRecorder v-if="showVideoRec" @close="showVideoRec=false; selectedIcon=null" @complete="videoRecCompleted" />

  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'
import { isAndroid, isIOS, osVersion } from 'mobile-device-detect'
import heic2any from 'heic2any'
import { VEmojiPicker } from 'v-emoji-picker'

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
    // %FIXME @niko - The 'onHide' code is for hiding the create post form in the vault page. If you remember, I added the Cancel button to the form on vault page when the user tries to add a vault image to be used in a new post. That's only visible in Vault page so onHide is not always available, that's why I added validity check: onHide && onHide() meaning, call this function only if it's defined, but I think it's wrong code, now that I think about it. I'll push a quick fix 
  },

  computed: {

    ...Vuex.mapState('vault', [
      'mobile',
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),

    isIOS9PlusAndAndroid() {
      return (isIOS && parseInt(osVersion.split('.')[0]) >= 9) || isAndroid
    },

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
        clickable: '#clickme_to-select', // %FIXME: should be a class or a much more specific ID to avoid duplicates/conflicts elsehwere in markup?
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
      const descriptionWithoutAdminTags = this.description.replace(/\B#[\w\-.]+(!)?/g,'').trim()
      return this.isBusy || (!descriptionWithoutAdminTags && ( this.selectedMediafiles && this.selectedMediafiles.length===0 ))
    },


  }, // computed

  data: () => ({
    moment,
    newPostId: null,
    description: '',
    descriptionForEditor: '',
    selectedIcon: null, // 'pic',
    isHashtagIconSelected: false,
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
    isEmojiBoxVisible: false,
    lastRange: null,
    suggestions: [],
    lastMatches: [],
    newMatch: null,
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
      this.selectedIcon = null // 'pic'
      this.isHashtagIconSelected = false
      this.ptype = 'free'
      this.price = 0
      this.priceForPaidSubscribers = 0
      this.scheduled_at = null
      this.expirationPeriod = null
      this.descriptionForEditor = ''
    },

    parseHashtags(searchText) {
      //const regexp = /\B\#\w\w+\b/g
      const regexp = /\B#[\w-.]+(!)?/g
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
      this.descriptionForEditor = this.description
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
      const json = response.data

      // (2) upload & attach the mediafiles (in dropzone queue)
      if (json.post) {
        this.newPostId = json.post.id
        const queued = this.$refs.myVueDropzone.getQueuedFiles()

        // %FIXME: if this fails, don't we have an orphaned post (?)
        // %NOTE: files added manually don't seem to be put into the queue, thus onDropzoneSending won't be called for them (?)

        if (queued.length) {
          this.uploadingFilesCount = queued.length
          this.$refs.myVueDropzone.processQueue() // this will call createCompleted() via callback
        }  else {
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
        // upload failed, delete the post and clean-up
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
      this.selectedIcon = this.selectedIcon!=='pic' ? 'pic' : null // toggle
    },
    recordVideo() { // %TODO
      this.selectedIcon = this.selectedIcon!=='video' ? 'video' : null // toggle
      this.showVideoRec = true
    },
    recordAudio() { // %TODO
      this.selectedIcon = this.selectedIcon!=='audio' ? 'audio' : null // toggle
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

    showTagForm() { // toggles visiblity
      if ( this.isTagFormVisible && this.isHashtagIconSelected ) { 
        // toggling to hidden, deselect icon if selected
        this.isHashtagIconSelected = false
      } else {
        // toggling to viewable, always select icon
        this.isHashtagIconSelected = true
      }
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
    },

    async getMatches(text) {
      const params = {
        term: text,
        field: 'slug',
      }
      const response = await axios.get( this.$apiRoute('users.match'), { params } )
      this.suggestions = response.data;
    },

    compareMatches(a, b) {
      if (a.length >= b.length) {
        let i = 0;
        while(a[i] == b[i] && i < a.length) {
          i++;
        }
        if (i < a.length) {
          return a[i];
        }
      } else {
        let i = 0;
        while(a[i] == b[i] && i < b.length) {
          i++;
        }
        if (i < b.length) {
          return b[i];
        }
      }
    },

    onInput(e) {
      this.lastRange = this.saveSelection()
      const cursorPos = this.lastRange.startOffset
      const fontEle = e.target.querySelector('font')
      if (fontEle) {
        fontEle.outerHTML = `<span>${fontEle.innerText}</span>`;
        this.restoreSelection(this.lastRange)
      }
      const anchors = e.target.querySelectorAll('a');
      if (anchors.length > 0) {
        anchors.forEach(anchor => {
          if (anchor.innerText[0] != '@') {
            anchor.outerHTML = `<span>${anchor.innerText}</span>`;
            this.restoreSelection(this.lastRange)
          }
        })
      }
      let html = e.target.innerHTML
      const matches = html.match(/\B(@[\w\-.]+)/g) || []
      if (matches.length > 0) {
        this.newMatch = this.compareMatches(matches, this.lastMatches);
        if (html.search(`<a>${this.newMatch}</a>`) > -1) {
          if (anchors.length > 0) {
            anchors.forEach(anchor => {
              if (anchor.innerText == this.newMatch) {
                const randClass = `s${new Date().getTime()}`;
                anchor.outerHTML = `<span class='${randClass}'>${this.newMatch.substring(0, cursorPos)}</span><span>${this.newMatch.substring(cursorPos)}</span>`;
                this.setCursorPosition('.' + randClass)
              }
            })
          }
          this.description = e.target.innerHTML
        } else {
          this.description = html
        }
        if (this.newMatch) {
          this.lastMatches = matches;
          this.getMatches(this.newMatch.slice(1));
        } else {
          this.suggestions = [];
        }
      } else {
        this.description = html;
        this.newMatch = null;
        this.suggestions = [];
      }
    },

    selectSuggestion(suggestion) {
      this.restoreSelection(this.lastRange)
      this.pasteHtmlAtCaret(`<a>@${suggestion.label}</a>&nbsp;`)
      const ele = document.querySelector('.create_post-crate .text-editor')
      this.description = ele.innerHTML
      this.suggestions = []
      this.lastMatches = this.description.match(/\B(@[\w\-.]+)/g) || []
    },

    editorClicked(e) {
      this.lastRange = this.saveSelection()
      if (e.target.tagName == 'A') {
        const url = e.target.textContent.slice(1)
        window.location.href = url;
      }
    },

    saveSelection() {
      if (window.getSelection) {
        let sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
          return sel.getRangeAt(0);
        }
      } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
      }
      return null;
    },

    restoreSelection(range) {
      if (range) {
        if (window.getSelection) {
          let sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        } else if (document.selection && range.select) {
          range.select();
        }
      }
    },

    setCursorPosition(ele) {
      const p = document.querySelector(ele),
          s = window.getSelection(),
          r = document.createRange();
      r.setStart(p, 1);
      r.setEnd(p, 1);
      s.removeAllRanges();
      s.addRange(r);
    },

    pasteHtmlAtCaret(html) {
      let sel, range;
      if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
          range = sel.getRangeAt(0);
          range.deleteContents();

          // Range.createContextualFragment() would be useful here but is
          // non-standard and not supported in all browsers (IE9, for one)
          const el = document.createElement("span");
          el.innerHTML = html;
          let frag = document.createDocumentFragment(), node, lastNode;
          while ( (node = el.firstChild) ) {
            lastNode = frag.appendChild(node);
          }
          range.insertNode(frag);

          const text = range.startContainer.textContent
          if (text && this.newMatch) {
            range.startContainer.textContent = text.substring(0, text.length - this.newMatch.length)
          }
          
          // Preserve the selection
          if (lastNode) {
            range = range.cloneRange();
            range.setStartAfter(lastNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
          }
        }
      } else if (document.selection && document.selection.type != "Control") {
        // IE < 9
        document.selection.createRange().pasteHTML(html);
      }
      this.lastRange = this.saveSelection()
    },


    selectEmoji(emoji) {
      const ele = document.querySelector('.create_post-crate .text-editor')
      ele.focus()
      this.restoreSelection(this.lastRange)
      this.pasteHtmlAtCaret(emoji.data)
      this.description = ele.innerHTML
    },

    closeEmojiBox() {
      this.isEmojiBoxVisible = false;
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
      this.isHashtagIconSelected = this.isTagFormVisible // highlight if we have tags
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
    VEmojiPicker,
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
  min-height: inherit !important;
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
    //height: 125px;
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

.select-emoji {
  position: relative;;
}
</style>

<style lang="scss">
  .text-editor {
    color: #383838;
    min-height: 70px;
    padding: 1em;
    background: #fff;

    &:focus {
      background: #fff;
    }

    a {
      cursor: pointer;
      color: var(--primary) !important;
    }

    p {
      margin-bottom: 0;
    }

    .emoji {
      // letter-spacing: 0.3em;
    }
  }

  .tag-post_desc {
    p {
      margin-bottom: 0;
    }
    .emoji {
      // letter-spacing: 0.3em;
    }
  }

  #EmojiPicker {
    position: absolute;
    z-index: 1000;
    top: 120%;
    left: 0;

    .container-emoji {
      .emoji {
        border: none !important;
      }
    }
  }

  @media (max-width: 576px) {
    #EmojiPicker {
      left: auto;
      right: 0px;
  
      .container-emoji {
        height: 160px;
      }
    }
  }

  .suggestion {
    border-radius: 5px;
    color: #fff;
    font-size: 12px;
    cursor: pointer;
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
