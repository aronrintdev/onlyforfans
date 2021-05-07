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
            <div v-if="postType === 'price'" class="w-100">
              <PriceSelector v-if="postType === 'price'" v-model="price" class="mb-3" />
              <hr />
            </div>

            <textarea v-model="description" rows="8" class="w-100"></textarea>
            <vue-dropzone
              ref="myVueDropzone"
              id="dropzone"
              :options="dropzoneOptions"
              :include-styling=true
              :useCustomSlot=true
              v-on:vdropzone-file-added="addedEvent"
              v-on:vdropzone-removed-file="removedEvent"
              v-on:vdropzone-sending="sendingEvent"
              v-on:vdropzone-success="successEvent"
              v-on:vdropzone-error="errorEvent"
              v-on:vdropzone-queue-complete="queueCompleteEvent"
              class="dropzone"
            >
              <!-- <label id="clickme_to-select" class="">Browse</label> -->
            </vue-dropzone>
          </div>
          <template #footer>
            <b-row>
              <b-col cols="12" md="8" class="d-flex">
                <ul class="list-inline d-flex mb-0 OFF-border-right">
                  <li><label id="clickme_to-select" class=""><b-icon icon="file-earmark" variant="secondary" font-scale="1.5"></b-icon></label></li>
                  <li @click="takePicture()" class="selectable select-pic"><b-icon icon="image" :variant="selectedMedia==='pic' ? 'primary' : 'secondary'" font-scale="1.5"></b-icon></li>
                  <li @click="recordVideo()" class="selectable select-video"><b-icon icon="camera-video" :variant="selectedMedia==='video' ? 'primary' : 'secondary'" font-scale="1.5"></b-icon></li>
                  <li @click="recordAudio()" class="selectable select-audio"><b-icon icon="mic" :variant="selectedMedia==='audio' ? 'primary' : 'secondary'" font-scale="1.5"></b-icon></li>
                </ul>
                <div class="border-right"></div>
                <ul class="list-inline d-flex mb-0">
                  <li class="selectable select-location"><span><LocationPinIcon /></span> </li>
                  <li class="selectable select-emoji"><span><EmojiIcon /></span></li>
                  <li class="selectable select-timer"><span><TimerIcon /></span></li>
                  <li class="selectable select-calendar"><span><CalendarIcon /></span></li>
                </ul>
              </b-col>
              <b-col cols="12" md="4">
                <ul class="list-inline d-flex justify-content-end mb-0 mt-3 mt-md-0">
                  <li class="w-100 mx-0"><button @click="savePost()" class="btn btn-submit btn-primary w-100">Post</button></li>
                </ul>
              </b-col>
            </b-row>
          </template>
        </b-card>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '@/app';
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import EmojiIcon from '@components/common/icons/EmojiIcon.vue';
import LocationPinIcon from '@components/common/icons/LocationPinIcon.vue';
import TimerIcon from '@components/common/icons/TimerIcon.vue';
import CalendarIcon from '@components/common/icons/CalendarIcon.vue';

import PriceSelector from '@components/common/PriceSelector'

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
  },

  data: () => ({

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
    currency: 'USD',

    // ref:
    //  ~ https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/SendAdditionalParamsDemo.vue
    //  ~ https://www.dropzonejs.com/#config-autoProcessQueue
    dropzoneOptions: {
      url: '/mediafiles',
      paramName: 'mediafile',
      //acceptedFiles: 'image/*, video/*, audio/*',
      maxFiles: null,
      autoProcessQueue: false,
      thumbnailWidth: 100,
      //clickable: false, // must be false otherwise can't focus on text area to type (!)
      clickable: '#clickme_to-select',
      maxFilesize: 15.9,
      addRemoveLinks: true,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    },
  }),

  methods: {

    resetForm() {
      this.$refs.myVueDropzone.removeAllFiles();
      this.description = '';
      this.newPostId = null;
      this.selectedMedia = 'pic';
      this.ptype = 'free';
      this.price = 0
    },

    async savePost() {
      // (1) create the post
      const response = await axios.post(this.$apiRoute('posts.store'), {
        timeline_id: this.timeline.id,
        description: this.description,
        type: this.postType,
        price: this.price,
        currency: this.currency,
      });
      this.$log.debug('savePost', { response });
      const json = response.data;
      this.newPostId = json.post.id;

      const queued = this.$refs.myVueDropzone.getQueuedFiles();

      // (2) upload & attach the mediafiles
      // %FIXME: if this fails, don't we have an orphaned post (?)
      if ( queued.length ) {
        this.$refs.myVueDropzone.processQueue(); // this will call dispatch after files uploaded
      } else {
        this.$log.debug('savePost: dispatching unshiftPostToTimeline...');
        this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId });
        this.resetForm();
      }
    },

    takePicture() { // %TODO
      this.selectedMedia = this.selectedMedia!=='pic' ? 'pic' : null
    },
    recordVideo() { // %TODO
      this.selectedMedia = this.selectedMedia!=='video' ? 'video' : null
    },
    recordAudio() { // %TODO
      this.selectedMedia = this.selectedMedia!=='audio' ? 'audio' : null
    },

    // for dropzone
    sendingEvent(file, xhr, formData) {
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
      this.$log.debug('queueCompleteEvent', { });
      this.$log.debug('queueCompleteEvent: dispatching unshiftPostToTimeline...');
      this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId });
      this.resetForm();
    },

  },

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
  },
}
</script>

<style scoped>
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

li .selectable {
  cursor: pointer;
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
</style>
