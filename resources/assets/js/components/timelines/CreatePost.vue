<template>
  <div class="create_post-crate tag-crate mb-5">

    <section class="row">
      <div class="col">

        <b-card header-tag="header" footer-tag="footer">
          <template #header>
            <h6 class="mb-0">New Post</h6>
          </template>
          <div>
            <vue-dropzone 
                ref="myVueDropzone" 
                id="dropzone" 
                :options="dropzoneOptions" 
                :include-styling=true
                :useCustomSlot=true
                v-on:vdropzone-sending="sendingEvent"
                v-on:vdropzone-success="successEvent"
                v-on:vdropzone-queue-complete="queueCompleteEvent"
                class="dropzone OFF-d-flex OFF-align-items-stretch"
                >
                <textarea v-model="description" rows="8" class="w-100 OFF-h-100"></textarea>
                <!--
                v-on:vdropzone-thumbnail="thumbnailEvent"
                  <div class="dropzone-custom-content">
                  <h3 class="dropzone-custom-title">Drag and drop to upload content!</h3>
                  <div class="subtitle">...or click to select a file from your computer</div>
                  </div>
                -->
            </vue-dropzone>
          </div>
          <template #footer>
              <ul class="list-inline d-flex mb-0">
                <li><span id="imageUpload"><ImageIcon /></span> </li>
                <li><span id="selfVideoUpload"><CameraIcon /></span> </li>
                <li><span id="voiceRecord"><MicIcon /></span> </li>
                <li><span id="locationUpload"><LocationPinIcon /> </span> </li>
                <li><span id="emoticons"><EmojiIcon /></span> </li>
                <li><span id="emoticons"><TimerIcon /></span> </li>
                <li><span id="emoticons"><CalendarIcon /></span> </li>
                <li class="ml-auto"><button @click="savePost()" class="btn btn-submit btn-success">Post</button></li>
              </ul>
          </template>
        </b-card>
      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '../../app';
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import ImageIcon from '../common/icons/ImageIcon.vue';
import CameraIcon from '../common/icons/CameraIcon.vue';
import EmojiIcon from '../common/icons/EmojiIcon.vue';
import MicIcon from '../common/icons/MicIcon.vue';
import LocationPinIcon from '../common/icons/LocationPinIcon.vue';
import TimerIcon from '../common/icons/TimerIcon.vue';
import CalendarIcon from '../common/icons/CalendarIcon.vue';

export default {

  props: {
  },

  computed: {
    ...Vuex.mapState(['session_user']),
    ...Vuex.mapState(['timeline']),
  },

  data: () => ({

    description: '',
    newPostId: null,

    // ref: 
    //  ~ https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/SendAdditionalParamsDemo.vue
    //  ~ https://www.dropzonejs.com/#config-autoProcessQueue
    dropzoneOptions: {
      url: '/mediafiles',
      paramName: 'mediafile',
      autoProcessQueue: false,
      thumbnailWidth: 100,
      clickable: false,
      maxFilesize: 3.9,
      addRemoveLinks: true,
      headers: { 
        'X-Requested-With': 'XMLHttpRequest', 
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    },
  }),

  created() {
    this.$store.dispatch('getMe');
  },

  methods: {

    resetForm() {
      this.description = '';
      this.newPostId = null;
      this.$refs.myVueDropzone.removeAllFiles();
    },


    async savePost() {
      // (1) create the post
      const response = await axios.post(`/posts`, {
        timeline_id: this.timeline.id,
        description: this.description,
      });
      console.log('savePost', { response });
      const json = response.data;
      this.newPostId = json.post.id;

      const queued = this.$refs.myVueDropzone.getQueuedFiles();

      // (2) upload & attach the mediafiles
      if ( queued.length ) {
        this.$refs.myVueDropzone.processQueue(); // this will call dispatch after files uploaded
      } else {
        console.log('savePost: dispatching unshiftPostToTimeline...');
        this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId });
        this.resetForm();
      }
    },

    // for dropzone
    sendingEvent(file, xhr, formData) {
      console.log('sendingEvent', {
        file,
        formData,
        xhr,
      });
      if ( !this.newPostId ) {
        throw new Error('Cancel upload, invalid post id');
      }
      formData.append('resource_id', this.newPostId);
      formData.append('resource_type', 'posts');
      formData.append('mftype', 'post');
    },

    // for dropzone
    successEvent(file, response) {
      console.log('successEvent', {
        file, response,
      });
    },

    queueCompleteEvent(file, xhr, formData) {
      console.log('queueCompleteEvent', {
        file, xhr, formData,
      });
      console.log('queueCompleteEvent: dispatching unshiftPostToTimeline...');
      //eventBus.$emit('unshift-post-to-timeline', this.newPostId);
      this.$store.dispatch('unshiftPostToTimeline', { newPostId: this.newPostId });
      this.resetForm();
    },

  },

  components: {
    vueDropzone: vue2Dropzone,
    EmojiIcon,
    ImageIcon,
    CameraIcon,
    MicIcon,
    LocationPinIcon,
    TimerIcon,
    CalendarIcon,
  },
}


    /*
    // https://rowanwins.github.io/vue-dropzone/docs/dist/#/custom-preview
    thumbnailEvent: function(file, dataUrl) {
      console.log('thumbnailEvent', {
        file,
        dataUrl,
      });
      //this.$refs.myVueDropzone.methods.thumbnail(file, 'http://placehold.it/500x500')

      return false;

      var j, len, ref, thumbnailElement;
      if (file.previewElement) {
        file.previewElement.classList.remove("dz-file-preview");
        ref = file.previewElement.querySelectorAll("[data-dz-thumbnail-bg]");
        for (j = 0, len = ref.length; j < len; j++) {
          thumbnailElement = ref[j];
          thumbnailElement.alt = file.name;
          thumbnailElement.style.backgroundImage = 'url("' + dataUrl + '")';
        }
        return setTimeout(((function(_this) {
          return function() {
            return file.previewElement.classList.add("dz-image-preview");
          };
        })(this)), 1);
      }
    },
    */
</script>

<style>
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

.create_post-crate .dropzone textarea {
  resize: none;
}

/*
.create_post-crate .dropzone .dz-image img {
  width: 128px;
}
*/

.create_post-crate footer ul li {
  margin-right: 1em;
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
</style>
