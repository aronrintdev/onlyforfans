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
          <b-list-group-item v-for="(vf, index) in children" :key="vf.guid" role="button" @click="!isShareMode ? doNav($event, vf.id) : null">
            {{ vf.vfname }}
            <span v-if="isShareMode"><button @click="toggleMarkShared($event, 'vaultfolders', vf.id)" type="button" class="btn btn-link ml-3">Share</button></span>
          </b-list-group-item>
        </b-list-group>

        <hr />

        <ul class="list-unstyled ctrl">
          <li v-if="!showCreateForm"><b-button @click="showCreateForm=true" variant="link">New Folder</b-button></li>
          <li v-if="!isShareMode"><b-button @click="isShareMode=true" variant="link">Share Files</b-button></li>
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

        <b-form @submit="shareFiles" v-if="isShareMode">
          <b-form-group>
            <b-form-input
              id="sharee"
              v-model="shareForm.sharee"
              type="text"
              placeholder="Enter user to share with"
              required
              ></b-form-input>
          </b-form-group>

          <section class="demo">
            <div v-if="selected" style="padding-top:10px; width: 100%;">
              You have selected <code>{{selected.name}}, the {{selected.race}}</code>
            </div>
            <div class="autosuggest-container">
              <vue-autosuggest
                v-model="query"
                :suggestions="filteredOptions"
                @focus="focusMe"
                @click="clickHandler"
                @input="onInputChange"
                @selected="onSelected"
                :get-suggestion-value="getSuggestionValue"
                :input-props="{id:'autosuggest__input', placeholder:'Do you feel lucky, punk?'}">
                <div slot-scope="{suggestion}" style="display: flex; align-items: center;">
                  <img :style="{ display: 'flex', width: '25px', height: '25px', borderRadius: '15px', marginRight: '10px'}" :src="suggestion.item.avatar" />
                  <div style="{ display: 'flex', color: 'navyblue'}">{{suggestion.item.name}}</div>
                </div>
              </vue-autosuggest>
            </div>
          </section>
          <b-button type="submit" variant="primary">Share Selected</b-button>
          <b-button @click="cancelShareFiles" type="cancel" variant="secondary">Cancel</b-button>
        </b-form>

      </aside>

      <main class="col-md-9 OFF-d-flex OFF-align-items-center">

        <!-- +++ File Thumbnails +++ -->
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

        <!-- +++ File List +++ -->
        <section class="row mt-5">
          <div class="col-sm-12">
            <b-list-group>
              <b-list-group-item v-for="(mf) in mediafiles" :key="mf.guid" 
                @click="false ? getLink($event, mf.id) : null"
                role="button" 
                v-bind:class="{ 'tag-shared': isShareMode && isMarkShared({shareable_type: 'mediafiles', shareable_id: mf.id}) }"
                >
                <span>{{ mf.orig_filename }}</span>
                <span v-if="isShareMode"><button @click="toggleMarkShared($event, 'mediafiles', mf.id)" type="button" class="btn btn-link ml-3">Share</button></span>
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
import { VueAutosuggest } from 'vue-autosuggest'; // https://github.com/darrenjennings/vue-autosuggest#examples
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

    filteredOptions() {
      return [
        { 
          data: this.suggestions[0].data.filter(option => {
            return option.name.toLowerCase().indexOf(this.query.toLowerCase()) > -1;
          })
        }
      ];
    },
  },

  data: () => ({

    showCreateForm: false,

    isShareMode: false,

    markShared: [],

    createForm: {
      vfname: '',
      //vault_id: this.vault_pkid,
      //parent_id: this.currentFolderPKID,
    },
    shareForm: {
      sharee: '',
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
    },

    // ---

    query: "",
    selected: "",
    suggestions: [{
      data: [
        { id: 1, name: "Frodo", race: "Hobbit", avatar: "https://upload.wikimedia.org/wikipedia/en/thumb/4/4e/Elijah_Wood_as_Frodo_Baggins.png/220px-Elijah_Wood_as_Frodo_Baggins.png" },
        { id: 2, name: "Samwise", race: "Hobbit", avatar: "https://upload.wikimedia.org/wikipedia/en/thumb/7/7b/Sean_Astin_as_Samwise_Gamgee.png/200px-Sean_Astin_as_Samwise_Gamgee.png" },
        { id: 3, name: "Gandalf", race: "Maia", avatar: "https://upload.wikimedia.org/wikipedia/en/thumb/e/e9/Gandalf600ppx.jpg/220px-Gandalf600ppx.jpg" },
        { id: 4, name: "Aragorn", race: "Human", avatar: "https://upload.wikimedia.org/wikipedia/en/thumb/3/35/Aragorn300ppx.png/150px-Aragorn300ppx.png" }
      ],
    }]
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

    isMarkShared({shareable_type, shareable_id}) {
      return this.markShared.some( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id );
    },

    toggleMarkShared(e, shareable_type, shareable_id) {
      // toggle adding/removing this resource from the shared list
      const index = this.markShared.findIndex( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id );
      if (index !== -1) {
        this.markShared.splice(index, 1); // remove
      } else {
        this.markShared.push({ shareable_type, shareable_id }); // add
      }
    },

    shareFiles(e) {
    },

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
    cancelShareFiles() {
      this.isShareMode = false;
      this.shareForm.sharee = '';
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


    // ---

    clickHandler(item) {
      // event fired when clicking on the input
    },
    onSelected(item) {
      console.log('onSelected', {
        item
      });
      this.selected = item.item;
    },
    onInputChange(text) {
      // event fired when the input changes
      console.log(text)
    },
    // This is what the <input/> value is set to when you are selecting a suggestion.
    getSuggestionValue(suggestion) {
      return suggestion.item.name;
    },
    focusMe(e) {
      console.log(e) // FocusEvent
    },

  },

  watch: {
    mediafiles (newVal, oldVal) {
      this.loadDropzone(newVal);
    },
  },

  components: {
    vueDropzone: vue2Dropzone,
    VueAutosuggest,
  },
}
</script>

<style scoped>
/* --- */

.demo { 
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

input {
  width: 260px;
  padding: 0.5rem;
}

ul {
  width: 100%;
  color: rgba(30, 39, 46,1.0);
  list-style: none;
  margin: 0;
  padding: 0.5rem 0 .5rem 0;
}
li {
  margin: 0 0 0 0;
  border-radius: 5px;
  padding: 0.75rem 0 0.75rem 0.75rem;
  display: flex;
  align-items: center;
}
li:hover {
  cursor: pointer;
}

.autosuggest-container {
  display: flex;
  justify-content: center;
  width: 280px;
}

#autosuggest { width: 100%; display: block;}
.autosuggest__results-item--highlighted {
  background-color: rgba(51, 217, 178,0.2);
}

.tag-shared {
  background-color: pink;
}
</style>

