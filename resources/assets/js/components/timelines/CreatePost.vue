<template>
  <div class="create_post-crate tag-crate">

    <section class="row">
      <div class="col">

        <b-card header-tag="header" footer-tag="footer">
          <template #header>
            <section class="d-flex">
              <div class="my-auto mr-3">
                <h6 class="mb-0">Create a Post</h6>
              </div>
              <div class="post_create-ctrl d-flex flex-grow-1">
                <b-form-select id="post-type" class="w-auto ml-auto" v-model="postType" :options="ptypes" required />
              </div>
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
            <div v-if="postType === 'price'" class="w-100 d-flex">
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
                  @remove="removeMediafile"
                />
              </template>
            </vue-dropzone>
            <AudioRecorder
              v-if="showAudioRec"
              @close="showAudioRec=false;selectedMedia=null"
              @complete="audioRecordFinished"
            />
          </div>
          <template #footer>
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
                  <li @click="renderVaultSelector()" class="selectable select-audio">
                    <fa-icon :icon="selectedMedia==='vault' ? ['fas', 'archive'] : ['far', 'archive']" size="lg" :class="selectedMedia==='vault' ? 'text-primary' : 'text-secondary'" />
                  </li>
                </ul>
                <div class="border-right"></div>
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
                <div class="border-right"></div>
                <ul class="list-inline d-flex mb-0 pt-1">
                  <li @click="showCampaignModal()" class="selectable select-pic" title="Start Promotional Campaign">
                    <fa-icon :icon="showedModal === 'campaign' ? ['fas', 'hand-holding-usd'] : ['far', 'hand-holding-usd']" size="lg" :class="showedModal === 'campaign' ? 'text-primary' : 'text-secondary'" />
                  </li>
                </ul>
              </b-col>
              <b-col cols="12" md="4">
                <ul class="list-inline d-flex justify-content-end mb-0 mt-3 mt-md-0">
                  <li class="w-100 mx-0">
                    <button :disabled="posting" @click="savePost()" class="btn btn-submit btn-primary w-100">
                      <span v-if="posting" class="text-white spinner-border spinner-border-sm pr-2" role="status" aria-hidden="true"></span>
                      Post
                    </button>
                  </li>
                </ul>
              </b-col>
            </b-row>
          </template>
        </b-card>
      </div>
    </section>
    <VideoRecorder v-if="showVideoRec" @close="showVideoRec=false; selectedMedia=null" @complete="videoRecCompleted" />
  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment';
import { isAndroid, isIOS, osVersion } from 'mobile-device-detect';

import { eventBus } from '@/eventBus'
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import EmojiIcon from '@components/common/icons/EmojiIcon.vue';
import LocationPinIcon from '@components/common/icons/LocationPinIcon.vue';
import TimerIcon from '@components/common/icons/TimerIcon.vue';
import CalendarIcon from '@components/common/icons/CalendarIcon.vue';

import PriceSelector from '@components/common/PriceSelector';
import UploadMediaPreview from '@components/posts/UploadMediaPreview';
import VideoRecorder from '@components/videoRecorder';
import AudioRecorder from '@components/audioRecorder';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    isIOS9PlusAndAndroid() {
      return (isIOS && parseInt(osVersion.split('.')[0]) >= 9) || isAndroid;
    },

    ...Vuex.mapState('vault', [
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),
  },

  data: () => ({
    moment,
    description: '',
    newPostId: null,
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


    // ref:
    //  ~ https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/SendAdditionalParamsDemo.vue
    //  ~ https://www.dropzonejs.com/#config-autoProcessQueue
    dropzoneOptions: {
      //url: '/mediafiles',
      url: route('mediafiles.store'),
      paramName: 'mediafile',
      //acceptedFiles: 'image/*, video/*, audio/*',
      maxFiles: null,
      autoProcessQueue: false,
      thumbnailWidth: 100,
      //clickable: false, // must be false otherwise can't focus on text area to type (!)
      clickable: '#clickme_to-select',
      maxFilesize: 15.9,
      addRemoveLinks: true,
      removeType: 'client',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    },
    scheduled_at: null,
    //mediafiles: [],
    posting: false,
    expirationPeriod: null,
    showVideoRec: false,
    showAudioRec: false,
  }),


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
      this.$refs.myVueDropzone.removeAllFiles();
      this.description = '';
      this.newPostId = null;
      this.selectedMedia = 'pic';
      this.ptype = 'free';
      this.price = 0;
      this.priceForPaidSubscribers = 0;
      this.scheduled_at = null;
      this.expirationPeriod = null;
    },

    async savePost() {
      this.posting = true;
      console.log('CreatePost::savePost()')
      // (1) create the post
      const response = await axios.post(this.$apiRoute('posts.store'), {
        timeline_id: this.timeline.id,
        description: this.description,
        type: this.postType,
        price: this.price,
        price_for_subscribers: this.priceForPaidSubscribers,
        currency: this.currency,
        schedule_datetime: this.scheduled_at?.toDate(),
        expiration_period: this.expirationPeriod,
      })
      this.$log.debug('savePost', { response })
      const json = response.data;

      // (2) upload & attach the mediafiles (in dropzone queue)
      if (json.post) {
        this.newPostId = json.post.id
        const queued = this.$refs.myVueDropzone.getQueuedFiles()

        // %FIXME: if this fails, don't we have an orphaned post (?)
        // %NOTE: files added manually don't seem to be put into the queue, thus onDropzoneSending won't be called for them (?)

        if (queued.length) {
          console.log('CreatePost::savePost() - process queue', { queued, })
          this.$refs.myVueDropzone.processQueue() // this will call createCompleted() via callback
        }  else {
          console.log('CreatePost::savePost() - nothing queued')
          this.createCompleted();
        }

      } else {
        this.resetForm();
        this.mediafiles = [];
        this.posting = false;
      }
    },

    // Dropzone: 'Modify the request and add addtional parameters to request before sending'
    onDropzoneSending(file, xhr, formData) {
      // %NOTE: file.name is the mediafile PKID
      this.$log.debug('onDropzoneSending', { file, formData, xhr });
      if ( !this.newPostId ) {
        throw new Error('Cancel upload, invalid post id');
      }
      formData.append('resource_id', this.newPostId);
      formData.append('resource_type', 'posts');
      formData.append('mftype', 'post');
    },

    // ------------ Dropzone ------------------------------------------------ //

    onDropzoneAdded(file) {
      /*
      if (!file.filepath) {
        this.mediafiles.push({
          type: file.type,
          name: file.name,
          filepath: URL.createObjectURL(file),
        });
      } else {
        this.mediafiles.push(file);
      }
      this.$log.debug('onDropzoneAdded')
       */
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

    onDropzoneRemoved(file, error, xhr) {
      /*
      this.$log.debug('onDropzoneRemoved')
       */
      const index = _.findIndex(this.selectedMediafiles, mf => (mf.filepath === file.filepath))
      if (index > -1)  {
        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

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
      /*
      this.$log.debug('onDropzoneError', { file, message, xhr });
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
      }
       */
      this.$log.error('Dropzone Error Event', { file, message, xhr })
      if (file) {
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
      console.log('onDropzoneQueueComplete', { });
      this.createCompleted();
    },

    removeFileFromSelected(file) {
      const index = _.findIndex(this.selectedMediafiles, mf => {
        return mf.upload ? mf.upload.filename === file.name : false
      })

      this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
    },

    // ---

    createCompleted() {
      this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId });
      this.$store.dispatch('getQueueMetadata');
      // Show notification if scheduled post is succesfully created
      if (this.scheduled_at) {
        this.$root.$bvToast.toast('New scheduled post is created.', {
          title: 'Success!',
          variant: 'primary',
          solid: true,
        })
      }
      this.resetForm();
      this.mediafiles = [];
      this.posting = false;
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

    // HERE
    //uploadFromVault() {
    //this.selectedMedia = this.selectedMedia!=='vault' ? 'vault' : null
    //// %FIXME: should add full upload from vault feature instead of redirecting
    //this.$router.push({ name: 'vault.dashboard' })
    //},

    renderVaultSelector() {
      eventBus.$emit('open-modal', {
        key: 'render-vault-selector',
        data: { 
          resource: this.timeline,
          resource_type: 'timelines', 
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

    changeMediafiles(data) {
      this.UPDATE_SELECTED_MEDIAFILES([...data])
    },

    onClearFiles() {
      this.$refs.myVueDropzone.removeAllFiles()
      this.CLEAR_SELECTED_MEDIAFILES()
    },

    removeMediafile(index) {
      /*
      const file = this.$refs.myVueDropzone.dropzone.files[index];
      if (file) {
        this.$refs.myVueDropzone.removeFile(file);
        this.mediafiles.splice(index, 1);
        this.mediafiles = [...this.mediafiles];
      }
       */
      if (index > -1)  {
        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },
    showExpirationPicker() {
      this.showedModal = 'expiration'
      eventBus.$emit('open-modal', {
        key: 'expiration-period',
      })
    },
    closeSchedulePicker(e) {
      this.scheduled_at = null;
      e.stopPropagation();
    },
    audioRecordFinished(file) {
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

    showCampaignModal() {
      this.showedModal = 'campaign'
      eventBus.$emit('open-modal', {
        key: 'modal-promotion-campaign',
      })
    },

    videoRecCompleted(file) {
      this.showVideoRec = false;
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

  },

  mounted() {
    const self = this;
    eventBus.$on('apply-schedule', function(data) {
      self.scheduled_at = data;
    })
    eventBus.$on('set-expiration-period', function(data) {
      self.expirationPeriod = data;
    })

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

  watch: {

    selectedMediafiles(value) {
      this.$log.debug('watch selectedMediafiles', { value })
    },

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
/*
.dropzone, .dropzone * {
  box-sizing: border-box;
}
.vue-dropzone {
  border: 2px solid #e5e5e5;
  font-family: Arial,sans-serif;
  letter-spacing: .2px;
  color: #777;
  transition: .2s linear;
}
.dropzone {
  min-height: 150px;
  border: 2px solid rgba(0, 0, 0, 0.3);
  background: white;
  padding: 20px 20px;
}
                               */

.card-header .dropdown {
  /*
  background-color: ...;
   */
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
}

/*
.create_post-crate .dropzone .dz-image img {
  width: 128px;
}
 */

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
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "priceForFollowers": "Price for free followers",
    "priceForSubscribers": "Price for paid subscribers",
  }
}
</i18n>
