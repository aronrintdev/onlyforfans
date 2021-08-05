<template>
  <section v-if="!isLoading" class="conversation-footer d-flex flex-column">

    <div v-if="isScheduled" class="scheduled-message-head d-flex justify-content-start align-items-center">
      <div>
        <fa-icon :icon="['fas', 'calendar-alt']" class="fa-lg" fixed-width />
        <span> Scheduled for </span>
        <strong>{{ moment(deliverAtTimestamp).local().format('MMM DD, h:mm a') }}</strong>
      </div>
      <b-button variant="link" @click="clearScheduled">
        <fa-icon :icon="['fas', 'times']" class="clickable fa-lg" fixed-width />
      </b-button>
    </div>

    <div class="store-chatmessage mt-auto">
      <SetPrice v-if="setPriceActive" v-model="newMessageForm.price" class="mt-3" />

      <AudioRecorder
        v-if="showAudioRec"
        class="mb-3"
        @close="showAudioRec=false"
        @complete="audioRecordFinished"
      />

      <VueDropzone
        ref="myVueDropzone"
        id="dropzone"
        :options="dropzoneOptions"
        include-styling
        useCustomSlot
        @vdropzone-file-added="onDropzoneAdded"
        @vdropzone-removed-file="onDropzoneRemoved"
        @vdropzone-sending="onDropzoneSending"
        @vdropzone-success="onDropzoneSuccess"
        @vdropzone-error="onDropzoneError"
        @vdropzone-queue-complete="onDropzoneQueueComplete"
        class="dropzone"
      >
        <!-- Photo Store display -->
        <div class="d-block w-100" v-if="selectedMediafiles && selectedMediafiles.length > 0">
          <div class="d-flex">
            <b-btn variant="link" size="sm" class="ml-auto" @click="onClearFiles">
              {{ $t('clearFiles') }}
            </b-btn>
          </div>
          <UploadMediaPreview
            :mediafiles="selectedMediafiles"
            @change="changeMediafiles"
            @openFileUpload="openDropzone"
            @remove="removeMediafile"
          />
        </div>

        <!-- Text area -->
        <div class="mt-3">
          <b-form-group>
            <b-form-textarea
              class="message"
              v-model="newMessageForm.mcontent"
              placeholder="Type a message..."
              :rows="mobile ? 2 : 3"
              max-rows="6"
              spellcheck="false"
              @keypress.enter="onEnterPress"
            ></b-form-textarea>
          </b-form-group>
        </div>
      </VueDropzone>

      <!-- Bottom Toolbar -->
      <Footer
        :selected="selectedOptions"
        @vaultSelect="toggleVaultSelect"
        @openScheduleMessage="openScheduleMessageModal"
        @recordAudio="recordAudio"
        @recordVideo="recordVideo"
        @setPrice="setPrice"
        @submit="sendMessage($event)"
      />
    </div>

    <VideoRecorder
      v-if="showVideoRec"
      @close="showVideoRec = false"
      @complete="videoRecCompleted"
    />

    <b-modal v-model="scheduleMessageOpen" body-class="p-0" hide-header centered hide-footer>
      <ScheduleDateTime
        :scheduled_at="newMessageForm.deliver_at"
        @apply-schedule="date => newMessageForm.deliver_at = date"
        @edit-apply-schedule="date => newMessageForm.deliver_at = date"
        @close="scheduleMessageOpen = false"
      />
    </b-modal>
  </section>
</template>

<script>
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'

import VueDropzone from 'vue2-dropzone'

import ScheduleDateTime from '@components/modals/ScheduleDateTime'
import UploadMediaPreview from '@components/posts/UploadMediaPreview'
import VideoRecorder from '@components/videoRecorder';
import AudioRecorder from '@components/audioRecorder';

import SetPrice from './SetPrice.vue'
import Footer from './Footer'

export default {
  name: 'NewMessageForm',

  components: {
    AudioRecorder,
    Footer,
    ScheduleDateTime,
    SetPrice,
    UploadMediaPreview,
    VideoRecorder,
    VueDropzone,
  },

  props: {
    session_user: null,
    chatthread_id: null,
    vaultOpen: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapState('messaging', [
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),

    channelName() {
      return `chatthreads.${this.chatthread_id}`
    },

    deliverAtTimestamp() {
      return this.isScheduled
        ? moment(this.newMessageForm.deliver_at).utc().unix()
        : null
    },

    isLoading() {
      return !this.session_user
    },

    isScheduled() {
      return this.newMessageForm.deliver_at !== null
    },

    dropzoneOptions() {
      return {
        url: this.$apiRoute('mediafiles.store'),
        paramName: 'mediafile',
        //acceptedFiles: 'image/*, video/*, audio/*',
        maxFiles: null,
        autoProcessQueue: false,
        thumbnailWidth: 100,
        //clickable: false, // must be false otherwise can't focus on text area to type (!)
        clickable: '.upload-files',
        maxFilesize: 15.9,
        addRemoveLinks: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
      }
    },

    selectedOptions() {
      var selected = []
      if (this.vaultOpen) {
        selected.push('vaultSelect')
      }
      if (this.scheduleMessageOpen) {
        selected.push('openScheduleMessage')
      }
      if (this.showVideoRec) {
        selected.push('recordVideo')
      }
      if (this.showAudioRec) {
        selected.push('recordAudio')
      }
      if (this.setPriceActive) {
        selected.push('setPrice')
      }

      return selected
    }

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: '',
      deliver_at: null,
      price: 0,
      currency: 'USD',
    },

    scheduleMessageOpen: false,
    showVideoRec: false,
    showAudioRec: false,

    setPriceActive: false,

    // If client is sending message
    sending: false,

  }), // data

  created() {
    this.isTyping = _.throttle(this._isTyping, 1000)
  },

  mounted() { },

  methods: {
    ...Vuex.mapMutations('messaging', [
      'ADD_SELECTED_MEDIAFILES',
      'CLEAR_SELECTED_MEDIAFILES',
      'UPDATE_SELECTED_MEDIAFILES',
      'REMOVE_SELECTED_MEDIAFILE_BY_INDEX',
    ]),

    ...Vuex.mapActions('messaging', [
      'getUploadsVaultFolder',
    ]),

    changeMediafiles(data) {
      this.UPDATE_SELECTED_MEDIAFILES([...data])
    },

    onClearFiles() {
      this.$refs.myVueDropzone.removeAllFiles()
      this.CLEAR_SELECTED_MEDIAFILES()
    },

    // ------------ Dropzone ------------------------------------------------ //

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },

    onDropzoneAdded(file) {
      this.$log.debug('onDropzoneAdded', {file})
      if (!file.filepath) {
        this.ADD_SELECTED_MEDIAFILES({
          ...file,
          type: file.type,
          filepath: URL.createObjectURL(file),
        })
      } else {
        this.ADD_SELECTED_MEDIAFILES({
          ...file,
          type: file.type,
        })
      }
      this.$nextTick(() => this.$forceUpdate())
    },

    onDropzoneRemoved(file) {
      const index = _.findIndex(this.selectedMediafiles, mf => (mf.filepath === file.filepath))
      if (index > -1)  {
        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

    removeMediafile(index) {
      if (index > -1)  {
        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

    /** Add to Dropzone formData */
    onDropzoneSending(file, xhr, formData) {
      if ( !this.uploadsVaultFolder ) {
        throw new Error('Cancel upload, invalid upload folder');
      }
      formData.append('resource_id', this.uploadsVaultFolder.id)
      formData.append('resource_type', 'vaultfolders')
      formData.append('mftype', 'vault')
    },

    // Called each time the queue successfully uploads a file
    onDropzoneSuccess(file, response) {
      this.$log.debug('onDropzoneSuccess', { file, response })
      // Remove Preview
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
      // Add Mediafile reference
      this.ADD_SELECTED_MEDIAFILES(response.mediafile)
    },

    onDropzoneError(file, message, xhr) {
      this.$log.error('Dropzone Error Event', { file, message, xhr })
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
    },

    onDropzoneQueueComplete() {
      this.finalizeMessageSend()
    },

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },

    removeFileFromSelected(file) {
      const index = _.findIndex(this.selectedMediafiles, mf => {
        return mf.upload ? mf.upload.filename === file.name : false
      })

      this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
    },

    //----------------------------------------------------------------------- //

    async finalizeMessageSend() {
      var params = {
        mcontent: this.newMessageForm.mcontent,
      }
      if (this.setPriceActive) {
        params.price    = this.newMessageForm.price
        params.currency = this.newMessageForm.currency
      }

      if (this.selectedMediafiles.length > 0) {
        params.attachments = this.selectedMediafiles
      }

      if (this.chatthread_id === 'new') {
        // %NOTE - Creating a new thread, delegate to parent template (CreateThreadForm), as
        //   that's where the selectedContact data resides
        params.is_scheduled = this.isScheduled
        if ( this.isScheduled ) {
          params.deliver_at = this.deliverAtTimestamp
        }
        this.$emit('create-chatthread', params)

      } else if (this.isScheduled) {
        // 'send' a pre-scheduled message (on an existing thread)
        params.deliver_at = this.deliverAtTimestamp
        await axios.post( this.$apiRoute('chatthreads.scheduleMessage', this.chatthread_id), params )
        this.$root.$bvToast.toast(
          this.$t('scheduled.message', { time: this.deliverAtTimestamp }),
          { variant: 'primary', title: this.$t('scheduled.title') }
        )
      } else {
        // send an immediate message (on an existing thread)
        const message = {
          chatthread_id: this.chatthread_id,
          mcontent: this.newMessageForm.mcontent,
          sender_id: this.session_user.id,
          is_delivered: true,
          imageCount: this.selectedMediafiles.length,
          created_at: this.moment().toISOString(),
          updated_at: this.moment().toISOString(),
        }
        this.$log.debug('messageForm sendMessage', { message })
        // Whisper the message to the channel so that is shows up for other users as fast as possible if they are
        //   currently viewing this thread
        this.$echo.join(this.channelName).whisper('sendMessage', { message })
        this.$emit('sendMessage', message)

        await axios.post( this.$apiRoute('chatthreads.sendMessage', this.chatthread_id), params )
      }

      this.clearForm()
      this.sending = false
    },

    async sendMessage() {
      this.sending = true
      // Validation check
      const mediafileCount = this.selectedMediafiles ? this.selectedMediafiles.length : 0
      console.log('sendMessage', mediafileCount)
      if (this.newMessageForm.mcontent === '' && mediafileCount === 0) {
        eventBus.$emit('validation', { message: this.$t('validation') })
        return
      }
      console.log(mediafileCount)
      if (this.newMessageForm.price > 0 && mediafileCount === 0) {
        eventBus.$emit('validation', { message: this.$t('pricedValidation')})
        return
      }

      // Process any file in the queue
      const queued = this.$refs.myVueDropzone.getQueuedFiles()
      this.$log.debug('sendMessage dropzone queue', { queued })
      if (queued.length > 0) {
        await this.getUploadsVaultFolder()
        this.$refs.myVueDropzone.processQueue()
      } else {
        this.finalizeMessageSend()
      }

    },

    /**
     * Send message on ctrl+enter
     */
    onEnterPress(e) {
      if (e.ctrlKey) {
        this.sendMessage()
      }
    },

    clearForm() {
      this.newMessageForm.mcontent = null
      this.clearPrice()
      this.CLEAR_SELECTED_MEDIAFILES()
      this.$refs.myVueDropzone.removeAllFiles()
      this.clearScheduled()
    },

    clearPrice() {
      this.setPriceActive = false
      this.newMessageForm.price = 0
      this.newMessageForm.currency = 'USD'
    },

    setScheduled: function() {
      this.$bvModal.hide('schedule-message-modal')
    },

    clearScheduled: function() {
      this.newMessageForm.deliver_at = null
      this.$bvModal.hide('schedule-message-modal')
    },

    doSomething() {
      // stub placeholder for impl
    },

    openScheduleMessageModal() {
      this.scheduleMessageOpen = true
    },

    toggleVaultSelect() {
      this.$emit('toggleVaultSelect')
    },

    recordAudio() {
      this.showAudioRec = !this.showAudioRec
    },

    audioRecordFinished(file) {
      this.showAudioRec = false
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

    recordVideo() {
      this.showVideoRec = !this.showVideoRec
    },

    videoRecCompleted(file) {
      this.showVideoRec = false;
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

    setPrice() {
      // Toggle on click
      this.setPriceActive = !this.setPriceActive
    },

    _isTyping() {
      this.$echo.join(this.channelName)
        .whisper('typing', {
          name: this.session_user.name || this.session_user.username
        })
    },

  }, // methods

  watch: {
    'newMessageForm.mcontent': function(value) {
      if (this.newMessageForm.deliver_at === undefined || this.newMessageForm.deliver_at === null) {
        if (value) {
          this.isTyping()
        }
      }
    },

    selectedMediafiles(value) {
      this.$log.debug('watch selectedMediafile', { value })
    },

  }, // watch



}
</script>

<style lang="scss" scoped>
.btn-link:hover {
  text-decoration: none;
}
.btn:focus, .btn.focus {
  box-shadow: none;
}

.conversation-footer {
  background-color: #fff;
  border-top: solid 1px rgba(138,150,163,.25);
}
button.clickme_to-submit_message {
  width: 9rem;
}

textarea,
.dropzone,
.vue-dropzone {
  border: none;
  &:hover {
    background-color: inherit;
  }
}

textarea {
  overflow-y: auto !important;
}

.dropzone.dz-started .dz-message {
  display: block;
}
.dropzone {
  padding: 0;
  min-height: 0 !important;
}

.dropzone .dz-message {
  width: 100%;
  text-align: center;
  margin: 0 !important;
}

textarea.form-control {
  border: none;
  overflow-y: auto;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "clearFiles": "Clear Images",
    "pricedValidation": "Priced images must contain media, please provide for this message",
    "scheduled": {
      "title": "Scheduled",
      "message": "Messages has successfully been schedule to send at {time}"
    },
    "validation": "Please enter a message or select files to send",
  }
}
</i18n>
