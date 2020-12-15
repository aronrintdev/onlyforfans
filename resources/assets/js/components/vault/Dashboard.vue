<template>
  <div class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Vault</h2>

        <h3 class="my-3">Vault
          > Root > {{ foo }}
        </h3>

        <b-list-group :children.sync="children">
          <b-list-group-item v-for="(f,idx) in children" :key="f.guid" role="button" @click="doNav($event, f.id)">
            {{ f.slug }}
          </b-list-group-item>
        </b-list-group>

        <hr />

        <ul>
          <li v-for="mf in attrs.mediafiles" :id="mf.id">
            {{ mf.slug }}
          </li>
        </ul>

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
import Vuex from 'vuex';
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import { eventBus } from '../../app';
//import TextStoryForm from './TextStoryForm.vue';

export default {

  props: {
    mediafiles: {
      type: Array,
      required: true,
    },
    parent: {
      //type: Object,
      required: true,
      validator: prop => typeof prop === 'Object' || prop === null
    },
    cwf: {
      type: Object,
      required: true,
    },
  },

  computed: {
        ...Vuex.mapState(['children']),
  },

  data: () => ({

    show: true,
    
    foo: 'bar baz',

    //m_mediafiles: [ ...this.mediafiles ],
    //m_children: [ ...this.children ],

    attrs: {
      //mediafiles: this.mediafiles,
      //children: this.children,
      parent: this.parent,
      cwf: this.cwf,
    },

    dropzoneOptions: {
      //previewTemplate: '<h2>foo</h2>', // %TODO: https://www.dropzonejs.com/#config-previewTemplate, https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/customPreviewDemo.vue
      url: '/mediafiles',
      paramName: 'mediafile',
      //url: 'https://httpbin.org/post',
      thumbnailHeight: 128,
      maxFilesize: 3.9,
      headers: { 
        'X-Requested-With': 'XMLHttpRequest', 
        //'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    }
  }),

  mounted() {
    console.log('mounted', {
      cwf: this.cwf, // use prop
    });
    this.preloadFolder();
  },

  created() {
    this.$store.dispatch('getChildren');
    /*
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
     */
  },


  methods: {

    doNav(e, vfId) {
      console.log('doNav', {
        event: e,
        val: vfId,
      });
      this.changeFolder(vfId);
    },

    sendingEvent(file, xhr, formData) {
      formData.append('resource_id', 1); // %FIXME hardcoded
      formData.append('resource_type', 'vaultfolders');
      formData.append('mftype', 'vault');
    },

    // Preload the mediafiles in the current folder (pwd)
    preloadFolder() {
      for ( let mf of this.mediafiles ) { // use prop
        var file = { 
          size: 1024, 
          name: mf.slug,
          type: mf.mimetype, // "image/png"
        };
        //var url = mf.filename;
        var url = mf.mf_url;
        this.$refs.myVueDropzone.manuallyAddFile(file, url);
      }
    },

    async changeFolder(vfId) {
      const url = `/vaultfolders/${vfId}`;

      const response = await axios.get(url, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
      //this.attrs.cwf = response.cwf;
      //this.attrs.parent = response.parent;
      this.m_children = response.children;
      this.foo = "here 123";
      //this.$emit('update:children', this.children)
      //this.attrs.mediafiles = response.mediafiles;
    },
  },

  components: {
    //textStoryForm: TextStoryForm,
    vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
</style>

