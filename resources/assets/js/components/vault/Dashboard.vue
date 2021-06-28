<template>
  <div v-if="!isLoading" class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Vault</h2>

        <hr />

        <b-breadcrumb>
          <b-breadcrumb-item v-for="(bc, index) in breadcrumbNav" :key="bc.pkid" @click="doNav(bc.pkid)" :active="bc.active">{{ bc.text }}</b-breadcrumb-item>
        </b-breadcrumb>

        <ul class="folder-nav pl-0">
          <TreeItem
            class="item"
            :item="foldertree"
            :currentFolderId="currentFolderId"
            @make-folder="makeFolder"
            @add-item="addItem"
            @do-nav="doNav"
          />
        </ul>

        <hr />

        <b-form @submit="shareFiles" v-if="true">

          <div v-if="selectedMediafiles.length" class="autosuggest-container">
            <b-form-group>
              <b-form-input
                id="invite-email"
                v-model="inviteeInput"
                v-on:keydown.enter.prevent="addInvite"
                type="text"
                placeholder="Enter email to invite..."
              ></b-form-input>
            </b-form-group>
            <!--
            <b-form-group>
              <vue-autosuggest
                v-model="query"
                :suggestions="filteredOptions"
                @focus="focusMe"
                @input="getMatches"
                @selected="addSharee"
                :get-suggestion-value="getSuggestionValue"
                :input-props="{id:'autosuggest__input', class: 'form-control', placeholder:'Enter user to share with...'}">
                <div slot-scope="{suggestion}" style="display: flex; align-items: center;">
                  <div style="{ display: 'flex' }">{{suggestion.item.label}}</div>
                </div>
              </vue-autosuggest>
            </b-form-group>
            -->
          </div>

          <b-button v-if="selectedMediafiles.length" type="submit" variant="primary">Share Selected</b-button>
          <!--
          <b-button @click="cancelShareFiles" type="cancel" variant="secondary">Cancel</b-button>
          -->
        </b-form>

        <!--
        <h4>Shares</h4>
        <ul v-if="shareForm.sharees.length">
          <li v-for="(se) in shareForm.sharees">
            {{ se.label }}
          </li>
        </ul>

        <h4>Invites</h4>
        <ul v-if="shareForm.invitees.length">
          <li v-for="(i) in shareForm.invitees">{{ i }}</li>
        </ul>
        -->

      </aside>

      <main class="col-md-9 OFF-d-flex OFF-align-items-center">

        <!-- +++ File Thumbnails / Dropzone File Uploader +++ -->
        <b-row v-if="isUploaderVisible">
          <b-col>
            <vue-dropzone 
              ref="myVueDropzone" 
              id="dropzone" 
              :options="dropzoneOptions"
              v-on:vdropzone-sending="sendingEvent"
              v-on:vdropzone-success="successEvent"
            ></vue-dropzone>
          </b-col>
        </b-row>

        <!-- +++ Minor Nav -->
        <b-row class="py-3">
          <b-col class="minor-nav">
            <section v-if="this.selectedMediafiles.length" class="minor-nav d-flex justify-content-between align-items-center">
              <div>
                <b-button @click="clearSelected()" variant="link" class="text-decoration-none">
                  <fa-icon :icon="['fas', 'times']" class="fa-lg" />
                </b-button>
                <span class="ml-3">{{ this.selectedMediafiles.length }} selected</span>
              </div>
              <div>
                <b-button v-if="this.selectedMediafiles.length" @click="renderShareForm()" variant="primary">Add To</b-button>
              </div>
            </section>
            <section v-else class="minor-nav d-flex justify-content-between align-items-center">
              <div>
                <b-button variant="link" class="" @click="isUploaderVisible=!isUploaderVisible">
                  <fa-icon :icon="['fas', 'upload']" size="lg" />
                </b-button>
                <b-button variant="link" class="" @click="renderNewFolderForm">
                  <fa-icon :icon="['fas', 'plus']" size="lg" />
                </b-button>
              </div>
            </section>
          </b-col>
        </b-row>

        <!-- +++ List/Grid Display of Files +++ -->
        <b-row :no-gutters="true">
          <b-col cols="12" md="3" v-for="(mf) in mediafiles" :key="mf.id" role="button">
            <PreviewFile
              :data-mf_id="mf.id"
              class="p-1"
              :mediafile="mf"
              @input="onPreviewFileInput"
            />
          </b-col>
        </b-row>

      </main>

    </section>

    <b-modal id="modal-share-file" size="lg" title="Share Files" hide-footer body-class="p-0" >
      <div>
        <b-list-group>
          <b-list-group-item>
            <b-button @click="sendSelected('post')" variant="link" class="text-decoration-none">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mx-2" size="lg" />
              Send in New Post
            </b-button>
          </b-list-group-item>
          <b-list-group-item>
            <b-button @click="sendSelected('story')" variant="link" class="text-decoration-none">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mx-2" size="lg" />
              Send in New Story
            </b-button>
          </b-list-group-item>
          <b-list-group-item>
            <b-button @click="sendSelected('message')" variant="link" class="text-decoration-none">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mx-2" size="lg" />
              Send in New Message
            </b-button>
          </b-list-group-item>
        </b-list-group>
      </div>
    </b-modal>

    <b-modal id="modal-create-folder" v-model="isCreateFolderModalVisible" size="lg" title="Create Folder" body-class="OFF-p-0" >
      <b-form @submit.prevent="storeFolder">
          <b-form-group>
            <b-form-input
              id="folder-name"
              v-model="createForm.name"
              type="text"
              placeholder="Enter new folder name"
              required
            ></b-form-input>
          </b-form-group>
        </b-form>
        <template #modal-footer>
          <b-button @click="storeFolder" variant="primary">Create Folder</b-button>
          <b-button @click="isCreateFolderModalVisible=false" type="cancel" variant="secondary">Cancel</b-button>
        </template>
    </b-modal>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import vue2Dropzone from 'vue2-dropzone';
import { VueAutosuggest } from 'vue-autosuggest'; // https://github.com/darrenjennings/vue-autosuggest#examples
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import PreviewFile from '@components/vault/PreviewFile'
import TreeItem from '@components/vault/TreeItem'

export default {

  props: {
    vault_pkid: {
      required: true,
      type: String,
    },
    vaultfolder_pkid: { // init value
      required: true,
      type: String,
    },
  },

  computed: {
    //...Vuex.mapState(['vault']),
    ...Vuex.mapState(['vaultfolder']),
    ...Vuex.mapState(['breadcrumb']),
    ...Vuex.mapState(['shares']),

    isLoading() {
      return !this.vault_pkid || !this.vaultfolder_pkid || !this.vault || !this.vaultfolder || !this.foldertree
    },

    parent() {
      return this.vaultfolder.vfparent
    },
    children() {
      return this.vaultfolder.vfchildren
    },
    breadcrumbNav() {
      const result = []
      for ( let b of this.breadcrumb ) {
        const isActive = b.pkid === this.currentFolderId
        result.push({
          pkid: b.pkid,
          text: b.vfname,
          active: isActive,
        })
      }
      return result
    },

    filteredOptions() {
      return [{
        data: this.suggestions,
      }]
    },

    selectedMediafiles() {
      return _.filter(this.mediafiles, o => (o.selected))
    },
  },

  data: () => ({

    vault: null,
    foldertree: null,

    isUploaderVisible: false,
    isCreateFolderModalVisible: false,

    mediafiles: {}, // %FIXME: use array not keyed object!

    createForm: {
      name: '',
      //vault_id: this.vault_pkid,
      //parent_id: this.currentFolderId,
    },

    currentFolderId: null,

    inviteeInput: '',

    query: '',

    suggestions: [],

    shareForm: {
      sharees: [],
      invitees: [],
      selectedToShare: [],
    },

    dropzoneOptions: {
      url: route('mediafiles.index'),
      paramName: 'mediafile',
      thumbnailHeight: 128,
      maxFilesize: 15.9,
      headers: { 
        'X-Requested-With': 'XMLHttpRequest', 
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
    },

  }), // data

  created() {
    this.currentFolderId = this.vaultfolder_pkid
    //this.$store.dispatch('getVault', this.vault_pkid)
    this.axios.get(route('vaults.show', { id: this.vault_pkid })).then((response) => {
      console.log('vaults.show', { response } )
      this.vault = response.data.vault
      this.foldertree = response.data.foldertree || null
      this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid)
    })
  },

  methods: {

    // from Vue tree demo (TreeItem aka VaultNavigation)
    makeFolder() {
      console.log('makeFolder')
    },
    addItem() {
    },

    sendSelected(resourceType) {
      // send (share) selected files to a post, story, or message
      console.log('sendSelected', {
        resourceType,
      })
      const params = {
          mediafile_ids: this.selectedMediafiles.map( ({id}) => id )
      }

      switch (resourceType) {
        case 'story':
          this.$router.replace({ name: 'stories.dashboard', params });
          break;
        case 'post':
          this.$router.replace({ name: 'index', params });
          break;
        case 'message':
          this.$router.replace({ name: 'chatthreads.create', params });
          break;
      }
    },

    renderShareForm() {
      console.log('renderShareForm')
      this.$bvModal.show('modal-share-file')
    },

    onPreviewFileInput(value) {
      Vue.set(this.mediafiles, value.id, value) // Sets .selected on mediafiles array depending on child form component's action
    },

    clearSelected() {
      console.log('clearSelected')
      this.mediafiles = _.mapValues( this.mediafiles, o => ({ ...o, selected: false }) )
    },

    // In share mode, has the user selected this item to be shared
    isSelectedToShare({shareable_type, shareable_id}) {
      return this.shareForm.selectedToShare.some( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id )
    },

    // In non-share mode, is this item shared
    isShared(resourceType, resourceId) {
      return this.shares.some( o => o.shareable_type === resourceType && o.shareable_id === resourceId )
    },

    toggleSelectedToShare(e, shareable_type, shareable_id) {
      // toggle adding/removing this resource from the shared list
      const index = this.shareForm.selectedToShare.findIndex( o => o.shareable_type === shareable_type && o.shareable_id === shareable_id )
      if (index !== -1) {
        this.shareForm.selectedToShare.splice(index, 1) // remove
      } else {
        this.shareForm.selectedToShare.push({ shareable_type, shareable_id }) // add
      }
    },

    async shareFiles(e) {
      e.preventDefault()
      //const response = await axios.patch(`/vaults/${this.vault_pkid}/update-shares`, this.shareForm)
      const response = await axios.patch(`/vaults/${this.vault_pkid}/update-shares`, {
        shareables: this.shareForm.selectedToShare,
        sharees: this.shareForm.sharees.map( o => { return { sharee_id: o.id } }),
        invitees: this.shareForm.invitees.map( o => { return { email: o } }),
      })
      console.log('response', { response })
      this.cancelShareFiles()
    },

    cancelShareFiles() {
      //this.isShareMode = false
      this.shareForm.sharees = []
      this.shareForm.invitees = []
      this.shareForm.selectedToShare = []
      this.query = ''
      this.inviteeInput = ''
    },


    // --- New Vault Folder Form methods ---
    renderNewFolderForm() {
      this.isCreateFolderModalVisible = true
    },

    cancelCreateFolder() {
      this.isCreateFolderModalVisible = false
      this.createForm.name = ''
    },

    async storeFolder(e) {
      e.preventDefault()
      const payload = {
        vault_id: this.vault_pkid,
        parent_id: this.currentFolderId,
        vfname: this.createForm.name,
      }
      const response = axios.post('/vaultfolders', payload)
      console.log('response', { response })
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
      this.cancelCreateFolder()
    },

    //getLink(e, mediafileId) {
    //  axios.get(`/mediafiles/${mediafileId}`).then( (response) => {
    //    console.log('response', { response })
    //  })
    //},

    // for dropzone
    sendingEvent(file, xhr, formData) {
      formData.append('resource_id', this.currentFolderId)
      formData.append('resource_type', 'vaultfolders')
      formData.append('mftype', 'vault')
    },

    // for dropzone
    successEvent(file, response) {
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
    },

    // Preload the mediafiles in the current folder (pwd)
    async doNav(vaultfolderId) {
      console.log('doNav', {
        vaultfolderId,
      })
      this.currentFolderId = vaultfolderId
      this.$store.dispatch('getVaultfolder', vaultfolderId)
    },

    // ---

    addSharee(sharee) {
      console.log('addSharee', { sharee })
      this.shareForm.sharees.push(sharee.item)
      this.query = ''
      this.suggestions = []
    },

    addInvite(e) {
      const email = e.target.value
      if ( email && !this.shareForm.invitees.includes(email) ) {
        this.shareForm.invitees.push(email)
      }
      this.inviteeInput = ''
    },

    async getMatches(text) {
      const response = await axios.get(`/users/match?term=${text}&field=email`)
      this.suggestions = response.data
    },

    // This is what the <input/> value is set to when you are selecting a suggestion
    getSuggestionValue(suggestion) {
      return suggestion.item.label
    },

    focusMe(e) {
    },

  },

  watch: {
    vaultfolder (newVal, oldVal) {
      const selected = false
      this.mediafiles = _.keyBy(newVal.mediafiles.map(o => ({ ...o, selected })), 'id')
    },
  },

  components: {
    vueDropzone: vue2Dropzone,
    VueAutosuggest,
    TreeItem,
    PreviewFile,
  },
}
/*
      <b-row>
        <b-col v-for="(mf) in mediafiles" :key="mf.guid" 
          role="button" 
          v-bind:class="{ 'tag-shared': isShareMode && isSelectedToShare({shareable_type: 'mediafiles', shareable_id: mf.id}) }"
        >
          <div class="position-relative">
            <img class="OFF-img-fluid" height="64" :src="mf.filepath" />
            <b-form-checkbox ref="checkbox" size="lg" :checked="contact.selected" :value="true" @change="onSelect" />
          </div>
          <span>{{ mf.orig_filename }}</span>
          <span v-if="isShared('mediafiles', mf.id)"><b-icon icon="share"></b-icon></span>
          <span v-if="isShareMode"><button @click="toggleSelectedToShare($event, 'mediafiles', mf.id)" type="button" class="btn btn-link ml-3">Share</button></span>
        </b-col>
      </b-row>
      <b-list-group-item v-for="(vf, index) in children" :key="vf.guid" 
        @click="doNav($event, vf.id)"
        role="button" 
        v-bind:class="{ 'tag-shared': isShareMode && isSelectedToShare({shareable_type: 'vaultfolders', shareable_id: vf.id}) }"
      >
        {{ vf.name }} 
        <span v-if="isShared('vaultfolders', vf.id)"><b-icon icon="share"></b-icon></span>
        <span v-if="isShareMode"><button @click="toggleSelectedToShare($event, 'vaultfolders', vf.id)" type="button" class="btn btn-link ml-3">Share</button></span>
      </b-list-group-item>
 */
/*
      if ( this.contactsSelectedLength < this.contactsLength ) {
      this.selectIndeterminate = true
      }
      if ( this.contactsSelectedLength === this.contactsLength ) {
      this.selectIndeterminate = false
      this.selectAll = true
      }
      if (this.contactsSelectedLength === 0) {
      this.selectIndeterminate = false
      this.selectAll = false
      }
 */
/*
      async getContacts() {
      let params = {
      page: this.currentPage,
      take: this.perPage,
        //participant_id: this.session_user.id,
      }
      params = { ...params, ...this.filters }
      this.$log.debug('getContacts', {
      filters: this.filters,
      params: params,
      })
      if ( this.sortBy ) {
      params.sortBy = this.sortBy
      }
      const response = await axios.get( this.$apiRoute('mycontacts.index'), { params } )

      const selected = _.keys(this.filters).length > 0 ? true : false

      this.mycontacts = _.keyBy(response.data.data.map(o => ({ ...o, selected })), 'id')

      if (selected) {
      this.selectAll = true
      this.selectIndeterminate = false
      } else {
      this.selectAll = false
      }
      this.meta = response.meta
      },
 */
</script>

<style lang="scss" scoped>
body {
  ul.folder-nav {
    list-style: none;
  }
  .tag-shared {
    background-color: pink;
  }

  .vault-container {
    background: #fff;
  }
  .vue-dropzone {
    background: #ccdfeb;
  }
}
</style>
