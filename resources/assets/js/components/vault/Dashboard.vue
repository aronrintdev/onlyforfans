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
          <b-list-group-item v-for="(vf, index) in children" :key="vf.guid" 
                             @click="!isShareMode ? doNav($event, vf.id) : null"
                             role="button" 
                             v-bind:class="{ 'tag-shared': isShareMode && isMarkShared({shareable_type: 'vaultfolders', shareable_id: vf.id}) }"
                             >
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

          <div class="autosuggest-container">
            <b-form-group>
              <vue-autosuggest
                v-model="query"
                :suggestions="filteredOptions"
                @focus="focusMe"
                @click="clickHandler"
                @input="onInputChange"
                @selected="addSharee"
                :get-suggestion-value="getSuggestionValue"
                :input-props="{id:'autosuggest__input', class: 'form-control', placeholder:'Enter user to share with...'}">
                <div slot-scope="{suggestion}" style="display: flex; align-items: center;">
                  <div style="{ display: 'flex' }">{{suggestion.item.label}}</div>
                </div>
              </vue-autosuggest>
            </b-form-group>
          </div>

          <b-button type="submit" variant="primary">Share Selected</b-button>
          <b-button @click="cancelShareFiles" type="cancel" variant="secondary">Cancel</b-button>
        </b-form>

        <ul v-if="shareForm.sharees.length">
          <li v-for="(se) in shareForm.sharees">
            {{ se.label }}
          </li>
        </ul>

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
               v-on:vdropzone-success="successEvent"
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
                                 <img class="OFF-img-fluid" height="64" :src="mf.filepath" />
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

    /*
    suggestions() {
      return [];
    },
     */
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
      return [{
        data: this.suggestions,
      }];
      //this.$store.dispatch('getVaultfolder', this.currentFolderPKID);
      //this.cancelCreateFolder();
      /*
      return [{ 
          data: this.suggestions[0].data.filter(option => {
            return option.name.toLowerCase().indexOf(this.query.toLowerCase()) > -1;
          })
        }];
       */
    },
  },

  data: () => ({

    showCreateForm: false,

    isShareMode: true,

    createForm: {
      vfname: '',
      //vault_id: this.vault_pkid,
      //parent_id: this.currentFolderPKID,
    },

    shareForm: {
      sharees: [],
      markShared: [],
    },

    currentFolderPKID: null,

    dropzoneOptions: {
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
    suggestions: [],
  }),

  mounted() {
  },

  created() {
    this.currentFolderPKID = this.vaultfolder_pkid;
    this.$store.dispatch('getVault', this.vault_pkid);
    this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid);
  },

  methods: {

    isMarkShared({shareable_type, shareable_id}) {
      return this.shareForm.markShared.some( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id );
    },

    toggleMarkShared(e, shareable_type, shareable_id) {
      // toggle adding/removing this resource from the shared list
      const index = this.shareForm.markShared.findIndex( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id );
      if (index !== -1) {
        this.shareForm.markShared.splice(index, 1); // remove
      } else {
        this.shareForm.markShared.push({ shareable_type, shareable_id }); // add
      }
    },

    async shareFiles(e) {
      e.preventDefault();
      //const response = await axios.patch(`/vaults/${this.vault_pkid}/update-shares`, this.shareForm);
      const response = await axios.patch(`/vaults/${this.vault_pkid}/update-shares`, {
        shareables: this.shareForm.markShared,
        sharees: this.shareForm.sharees.map( o => { return {sharee_id: o.id}; }),
      });
      console.log('response', { response });
      //this.$store.dispatch('getVaultfolder', this.currentFolderPKID);
      this.cancelShareFiles();
    },

    storeFolder(e) {
      e.preventDefault();
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
      this.shareForm.sharees = [];
      this.shareForm.markShared = [];
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

    successEvent(file, response) {
      // %TODO: more efficient just to append the new file(s) (?)
      this.$store.dispatch('getVaultfolder', this.currentFolderPKID);
    },

    // Preload the mediafiles in the current folder (pwd)
    async doNav(e, vaultFolderPKID) {
      this.currentFolderPKID = vaultFolderPKID;
      this.$store.dispatch('getVaultfolder', vaultFolderPKID);
    },

    // ---

    clickHandler(item) {
      // event fired when clicking on the input
    },
    addSharee(item) {
      console.log('addSharee', { item });
      this.shareForm.sharees.push(item.item);
      this.query = '';
    },
    async onInputChange(text) {
      // event fired when the input changes
      //console.log('onInputChange', {text})
      const response = await axios.get(`/users/match?term=${text}&field=email`);
      //console.log('onInputChange', { response });
      this.suggestions = response.data;
    },
    // This is what the <input/> value is set to when you are selecting a suggestion.
    getSuggestionValue(suggestion) {
      console.log('getSuggestionValue', { suggestion });
      return suggestion.item.label;
    },
    focusMe(e) {
      console.log(e) // FocusEvent
    },

  },

  watch: {
    mediafiles (newVal, oldVal) {
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

/*
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
 */

.tag-shared {
  background-color: pink;
}
</style>

