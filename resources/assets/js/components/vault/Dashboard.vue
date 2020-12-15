<template>
  <div class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Vault</h2>
        <b-breadcrumb>
          <b-breadcrumb-item v-for="(item, index) in breadcrumbNav" :key="item.pkid" @click="doNav($event, item.pkid)" :active="item.active">{{ item.text }}</b-breadcrumb-item>
        </b-breadcrumb>

        <b-list-group>
          <b-list-group-item v-for="(vaultFolder, index) in children" :key="vaultFolder.guid" role="button" @click="doNav($event, vaultFolder.id)">
            {{ vaultFolder.vfname }}
          </b-list-group-item>
        </b-list-group>

        <hr />

        <ul>
          <li v-for="mf in mediafiles" :id="mf.id"> {{ mf.slug }} </li>
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

//import { eventBus } from '../../app';

export default {

  props: {
    vault_pkid: {
      required: true,
      type: Number,
    },
    vaultfolder_pkid: { // init value
      required: true,
      type: Number,
    },
  },

  computed: {
    ...Vuex.mapState(['vault']),
    ...Vuex.mapState(['vaultfolder']),
    ...Vuex.mapState(['breadcrumb']),

    mediafiles() {
      return this.vaultfolder.mediafiles;
    },
    parent() {
      return this.vaultfolder.vfparent;
    },
    children() {
      return this.vaultfolder.vfchildren;
    },
    breadcrumbNav() {
      const result = [];
      for ( let b of this.breadcrumb ) {
        const isActive = b.pkid === this.currentVaultFolderPKID;
        result.push({
          pkid: b.pkid,
          text: b.vfname,
          active: isActive,
        });
      }
      return result;
    },
  },

  watch: {
    mediafiles (newVal, oldVal) {
      this.loadDropzone(newVal);
    },
  },

  data: () => ({

    show: true,

    currentVaultFolderPKID: null,

    dropzoneOptions: {
      //previewTemplate: '<h2>foo</h2>', // %TODO: https://www.dropzonejs.com/#config-previewTemplate, https://github.com/rowanwins/vue-dropzone/blob/master/docs/src/pages/customPreviewDemo.vue
      url: '/mediafiles',
      paramName: 'mediafile',
      thumbnailHeight: 128,
      maxFilesize: 3.9,
      headers: { 
        'X-Requested-With': 'XMLHttpRequest', 
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    }
  }),

  mounted() {
    //console.log('mounted', { cwf: this.cwf, // use prop });
  },

  created() {
    this.currentVaultFolderPKID = this.vaultfolder_pkid;
    this.$store.dispatch('getVault', this.vault_pkid);
    this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid);
  },

  methods: {

    sendingEvent(file, xhr, formData) {
      formData.append('resource_id', this.currentVaultFolderPKID);
      formData.append('resource_type', 'vaultfolders');
      formData.append('mftype', 'vault');
    },

    // Preload the mediafiles in the current folder (pwd)
    loadDropzone(files) {
      this.$refs.myVueDropzone.removeAllFiles(true);
      for ( let mf of files ) { // use prop
        this.$refs.myVueDropzone.manuallyAddFile({
          size: 1024, 
          name: mf.slug,
          type: mf.mimetype, // "image/png"
        }, mf.filepath);
      }
    },

    async doNav(e, vaultFolderPKID) {
      this.currentVaultFolderPKID = vaultFolderPKID;
      this.$store.dispatch('getVaultfolder', vaultFolderPKID);
    },
  },

  components: {
    vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
</style>

