<template>
  <div class="create_post-crate tag-crate mb-5">

    <section class="row">
      <div class="col">

        <b-card header-tag="header" footer-tag="footer">
          <template #header>
            <section class="d-flex">
              <div class="my-auto mr-3">
                <h6 class="mb-0">New Post</h6>
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
              v-on:vdropzone-file-added="addedEvent"
              v-on:vdropzone-removed-file="removedEvent"
              v-on:vdropzone-sending="sendingEvent"
              v-on:vdropzone-success="successEvent"
              v-on:vdropzone-error="errorEvent"
              v-on:vdropzone-queue-complete="queueCompleteEvent"
              class="dropzone"
            >
              <div class="dz-custom-content">
                <textarea v-model="description" rows="8" class="w-100"></textarea>
              </div>
              <UploadMediaPreview
                :mediafiles="mediafiles"
                @change="changeMediafiles"
                @remove="removeMediafile"
                @openFileUpload="openDropzone"
              />
            </vue-dropzone>
          </div>
          <template #footer>
            <b-row>
              <b-col cols="12" md="8" class="post-create-footer-ctrl d-flex">
                <ul class="list-inline d-flex mb-0 OFF-border-right">
                  <li id="clickme_to-select" class="selectable select-pic">
                    <fa-icon :icon="['far', 'image']" :class="selectedMedia==='pic' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li v-if="!isIOS9Plus" @click="recordVideo()" class="selectable select-video">
                    <fa-icon :icon="['far', 'video']" :class="selectedMedia==='video' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="recordAudio()" class="selectable select-audio">
                    <fa-icon :icon="['far', 'microphone']" :class="selectedMedia==='audio' ? 'text-primary' : 'text-secondary'" />
                  </li>
                  <li @click="uploadFromVault()" class="selectable select-audio">
                    <fa-icon :icon="['far', 'archive']" :class="selectedMedia==='vault' ? 'text-primary' : 'text-secondary'" />
                  </li>
                </ul>
                <div class="border-right"></div>
                <ul class="list-inline d-flex mb-0">
                  <!--
                  <li class="selectable select-location"><span><LocationPinIcon /></span> </li>
                  <li class="selectable select-emoji"><span><EmojiIcon /></span></li>
                  <li class="selectable select-timer"><span><TimerIcon /></span></li>
                  <li class="selectable select-calendar" @click="showSchedulePicker()"><span><CalendarIcon /></span></li>
                  -->
                  <li class="selectable select-expire-date" :disabled="expirationPeriod" @click="showExpirationPicker()">
                    <fa-icon :icon="['far', 'hourglass-half']" class="text-secondary" />
                  </li>
                  <li class="selectable select-calendar" :disabled="scheduled_at" @click="showSchedulePicker()">
                    <fa-icon :icon="['far', 'calendar-check']" class="text-secondary" />
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
    <VideoRecorder v-if="showVideoRec" @close="showVideoRec=false; selectedMedia=null" />
  </div>
</template>

<script>
import moment from 'moment';
import { isIOS, osVersion } from 'mobile-device-detect';

import { eventBus } from '@/app';
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import EmojiIcon from '@components/common/icons/EmojiIcon.vue';
import LocationPinIcon from '@components/common/icons/LocationPinIcon.vue';
import TimerIcon from '@components/common/icons/TimerIcon.vue';
import CalendarIcon from '@components/common/icons/CalendarIcon.vue';

import PriceSelector from '@components/common/PriceSelector';
import UploadMediaPreview from '@components/posts/UploadMediaPreview';
import VideoRecorder from '@components/videoRecorder';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    isIOS9Plus() {
      return isIOS && parseInt(osVersion.split('.')[0]) >= 9;
    }
  },

  data: () => ({
    moment,
    description: '',
    newPostId: null,
    selectedMedia: null, // 'pic',
    postType: 'free',
    ptypes: [
      { text: 'Free', value: 'free' },
      { text: 'By Purchase', value: 'price' },
      { text: 'Subscriber-Only', value: 'paid' },
    ],
    price: 0,
    priceForPaidSubscribers: 0,
    currency: 'USD',

    mediafileIdsFromVault: [], // content added from vault, not disk: should create new references, *not* new S3 content!

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
    mediafiles: [],
    posting: false,
    expirationPeriod: null,
    showVideoRec: false,
  }),
  methods: {

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
      if (json.post) {
        this.newPostId = json.post.id
        const queued = this.$refs.myVueDropzone.getQueuedFiles()

        // (2) upload & attach the mediafiles (in dropzone queue)
        // %FIXME: if this fails, don't we have an orphaned post (?)
        // %NOTE: files added manually don't seem to be put into the queue, thus sendingEvent won't be called for them (?)

        // (3) create any mediaifle references, ex from selected files in vault
        if (this.mediafileIdsFromVault.length) {
          this.mediafileIdsFromVault.forEach( async mfid => {
            await axios.post(this.$apiRoute('mediafiles.store'), {
              mediafile_id: mfid, // the presence of this field is what tells controller method to create a reference, not upload content
              resource_id: json.post.id,
              resource_type: 'posts',
              mftype: 'post',
            })
            // %TODO: check failure case
          })
          this.mediafileIdsFromVault = [] // empty array (we could remove individually inside the loop)
          this.$router.replace({'query': null}).catch(()=>{}); // clear mediafile router params from URL
        } else if (queued.length) {
          console.log('CreatePost::savePost() - process queue', {
            queued,
          })
          this.$refs.myVueDropzone.processQueue() // this will call dispatch after files uploaded
        } 

        if ( !queued.length ) {
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
    sendingEvent(file, xhr, formData) {
      // %NOTE: file.name is the mediafile PKID
      this.$log.debug('sendingEvent', { file, formData, xhr });
      if ( !this.newPostId ) {
        throw new Error('Cancel upload, invalid post id');
      }
      formData.append('resource_id', this.newPostId);
      formData.append('resource_type', 'posts');
      formData.append('mftype', 'post');
    },

    // for dropzone
    addedEvent(file) {
      if (!file.filepath) {
        this.mediafiles.push({
          type: file.type,
          name: file.name,
          filepath: URL.createObjectURL(file),
        });
      } else {
        this.mediafiles.push(file);
      }
      this.$log.debug('addedEvent')
    },
    removedEvent(file, error, xhr) {
      this.$log.debug('removedEvent')
    },
    successEvent(file, response) {
      this.$log.debug('successEvent', { file, response, });
    },
    errorEvent(file, message, xhr) {
      this.$log.debug('errorEvent', { file, message, xhr });
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
      }
    },

    queueCompleteEvent() {
      // Retrieves the newly created post to display at top of feed
      // Not sure why but this event is invoked when image add fails (eg, drag & drop to dropzone fails), so protect against it
      if ( !this.newPostId ) {
        return
      }
      console.log('queueCompleteEvent', { });
      this.createCompleted();
    },

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
    },

    uploadFromVault() {
      this.selectedMedia = this.selectedMedia!=='vault' ? 'vault' : null
      // %FIXME: should add full upload from vault feature instead of redirecting
      this.$router.push({ name: 'vault.dashboard' })
    },

    showSchedulePicker() {
      eventBus.$emit('open-modal', {
        key: 'show-schedule-datetime',
        data: {
          scheduled_at: this.scheduled_at,
        }
      })
    },
    changeMediafiles(data) {
      this.mediafiles = [...data];
    },
    removeMediafile(index) {
      const file = this.$refs.myVueDropzone.dropzone.files[index];
      if (file) {
        this.$refs.myVueDropzone.removeFile(file);
        this.mediafiles.splice(index, 1);
        this.mediafiles = [...this.mediafiles];
      }
    },
    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },
    showExpirationPicker() {
      eventBus.$emit('open-modal', {
        key: 'expiration-period',
      })
    },
    closeSchedulePicker(e) {
      this.scheduled_at = null;
      e.stopPropagation();
    }
  },

  mounted() {
    const self = this;
    eventBus.$on('apply-schedule', function(data) {
      self.scheduled_at = data;
    })
    eventBus.$on('set-expiration-period', function(data) {
      self.expirationPeriod = data;
    })
    eventBus.$on('video-rec-complete', function(file) {
      self.showVideoRec = false;
      if (self.$refs.myVueDropzone) {
        self.$refs.myVueDropzone.addFile(file);
      }
      // self.$refs.myVueDropzone.manuallyAddFile(data, data.filepath);
    })

    if ( this.$route.params.context ) {
      switch( this.$route.params.context ) {
        case 'vault-via-postcreate': // we got here from the vault, likely with mediafiles to attach to a new post
          const mediafileIds = this.$route.params.mediafile_ids || []
          if ( mediafileIds.length ) {
            // Retrieve any 'pre-loaded' mediafiles, and add to dropzone...be sure to tag as 'ref-only' or something
            const response = axios.get(this.$apiRoute('mediafiles.index'), {
              params: {
                mediafile_ids: mediafileIds,
              },
            }).then( response => {
              response.data.data.forEach( mf => {
                // https://rowanwins.github.io/vue-dropzone/docs/dist/#/manual
                const file = { size: mf.orig_size, name: mf.id, type: mf.mimetype, filepath: mf.filepath }
                this.mediafileIdsFromVault.push(mf.id)
                this.$refs.myVueDropzone.manuallyAddFile(file, mf.filepath)
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
  },


  components: {
    PriceSelector,
    vueDropzone: vue2Dropzone,
    EmojiIcon, LocationPinIcon, TimerIcon, CalendarIcon,
    UploadMediaPreview,
    VideoRecorder,
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

.create_post-crate textarea,
.create_post-crate .dropzone,
.create_post-crate .vue-dropzone {
  border: none;
}

li.selectable {
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
