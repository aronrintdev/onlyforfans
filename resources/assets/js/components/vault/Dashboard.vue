<template>
  <div class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Vault</h2>

        <hr />

        <b-breadcrumb>
          <b-breadcrumb-item v-for="(item, index) in breadcrumbNav" :key="item.pkid" @click="doNav($event, item.pkid)" :active="item.active">{{ item.text }}</b-breadcrumb-item>
        </b-breadcrumb>

        <b-list-group>
          <b-list-group-item v-for="(vaultFolder, index) in children" :key="vaultFolder.guid" role="button" @click="doNav($event, vaultFolder.id)">
            {{ vaultFolder.vfname }}
          </b-list-group-item>
        </b-list-group>

        <hr />

        <ul v-if="!showCreateForm" class="list-unstyled ctrl">
          <li><b-button @click="showCreateForm=true" variant="link">New Folder</b-button></li>
        </ul>

        <b-form @submit="storeFolder" v-if="showCreateForm">
          <b-form-group>
            <b-form-input
              id="folder-name"
              v-model="createForm.vfname"
              type="text"
              placeholder="Enter new folder name"
              required
              ></b-form-input>
          </b-form-group>
          <b-button type="submit" variant="primary">Create Folder</b-button>
          <b-button @click="cancelCreateFolder" type="cancel" variant="secondary">Cancel</b-button>
        </b-form>

        <!--
          <hr />

        -->

      </aside>

      <main class="col-md-9 OFF-d-flex OFF-align-items-center">

        <section class="row">
          <div class="col-sm-12">
            <vue-dropzone 
               ref="myVueDropzone" 
               id="dropzone" 
               :options="dropzoneOptions"
               v-on:vdropzone-sending="sendingEvent"
               ></vue-dropzone>
          </div>
        </section>

        <section class="row mt-5">
          <div class="col-sm-12">
            <b-list-group>
              <b-list-group-item v-for="(mf) in mediafiles" :key="mf.guid" role="button" @click="getLink($event, mf.id)">
                {{ mf.orig_filename }}
              </b-list-group-item>
          </b-list-group>
          </div>
        </section>

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
        const isActive = b.pkid === this.currentFolderPKID;
        result.push({
          pkid: b.pkid,
          text: b.vfname,
          active: isActive,
        });
      }
      return result;
    },
  },

  data: () => ({

    show: true,

    showCreateForm: false,
    createForm: {
      vfname: '',
      //vault_id: this.vault_pkid,
      //parent_id: this.currentFolderPKID,
    },

    currentFolderPKID: null,

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
    this.currentFolderPKID = this.vaultfolder_pkid;
    this.$store.dispatch('getVault', this.vault_pkid);
    this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid);
  },

  methods: {

    storeFolder(e) {
      e.preventDefault()
      const payload = {
        vault_id: this.vault_pkid,
        parent_id: this.currentFolderPKID,
        vfname: this.createForm.vfname,
      };
      axios.post('/vaultfolders', payload).then( (response) => {
        console.log('response', { response });
        this.$store.dispatch('getVaultfolder', this.currentFolderPKID);
        this.cancelCreateFolder();
      });
    },

    cancelCreateFolder() {
      this.showCreateForm = false;
      this.createForm.vfname = '';
    },

    getLink(e, mediafilePKID) {
      axios.get(`/mediafiles/${mediafilePKID}`).then( (response) => {
        console.log('response', { response });
        //this.$store.dispatch('getVaultfolder', this.currentFolderPKID);
        //this.cancelCreateFolder();
      });
    },

    sendingEvent(file, xhr, formData) {
      formData.append('resource_id', this.currentFolderPKID);
      formData.append('resource_type', 'vaultfolders');
      formData.append('mftype', 'vault');
    },

    // Preload the mediafiles in the current folder (pwd)
    loadDropzone(files) {
      this.$refs.myVueDropzone.removeAllFiles();
      for ( let mf of files ) { // use prop
        this.$refs.myVueDropzone.manuallyAddFile({
          size: 1024, 
          name: mf.slug,
          type: mf.mimetype, // "image/png"
        }, mf.filepath);
      }
    },

    async doNav(e, vaultFolderPKID) {
      this.currentFolderPKID = vaultFolderPKID;
      this.$store.dispatch('getVaultfolder', vaultFolderPKID);
    },
  },

  watch: {
    mediafiles (newVal, oldVal) {
      this.loadDropzone(newVal);
    },
  },

  components: {
    vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
</style>

