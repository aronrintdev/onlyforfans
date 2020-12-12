<template>
  <div class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Vault</h2>

      </aside>

      <main class="col-md-9 d-flex align-items-center">
        <vue-dropzone 
          ref="myVueDropzone" 
          id="dropzone" 
          :options="dropzoneOptions"
          v-on:vdropzone-sending="sendingEvent"
          ></vue-dropzone>
      </main>

    </section>
  </div>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import { eventBus } from '../../app';
//import TextStoryForm from './TextStoryForm.vue';

export default {

  props: {
    /*
    dtoUser: {
      type: Object,
      required: true
    },
    stories: {
      type: Array,
      required: true
    },
    */
  },

  data: () => ({

    show: true,

    storyAttrs: {
      contents: '',
      color: '#fff',
    },

    dropzoneOptions: {
        url: '/mediafiles',
        paramName: 'mediafile',
        //url: 'https://httpbin.org/post',
        thumbnailWidth: 150,
        maxFilesize: 3.9,
        headers: { 
          'X-Requested-With': 'XMLHttpRequest', 
          //'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
    }
  }),

  mounted() {
  },

  created() {
    /*
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
    */
  },


  methods: {
    sendingEvent (file, xhr, formData) {
      formData.append('resource_id', 1); // %FIXME hardcoded
      formData.append('resource_type', 'vaultfolders');
      formData.append('mftype', 'vault');
    },
    /*
    async shareStory() {
      const url = `/${this.dtoUser.username}/stories`;
      let payload = new FormData();
      const json = JSON.stringify({
        stype: this.stype,
        bgcolor: this.storyAttrs.color || null,
        content: this.storyAttrs.contents,
      });
      payload.append('attrs', json);

      switch ( this.stype ) {
        case 'text':
          break;
        case 'image':
          payload.append('mediafile', this.mediafile);
          break;
      } 

      const response = await axios.post(url, payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
      this.step = this.steps.SELECT_STYPE;
    },
      */
  },

  components: {
    //textStoryForm: TextStoryForm,
    vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
</style>

