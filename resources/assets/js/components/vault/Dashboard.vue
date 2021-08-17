<template>
  <div v-if="!isLoading" class="container-fluid vault-container h-100">

    <b-row class="pt-2">
      <b-col>
        <h2 class="my-1">My Vault</h2>
        <hr class="my-0"/>
      </b-col>
    </b-row>

    <b-row class="mt-3">

      <aside class="col-md-3 d-none d-lg-block">
        <b-button
          variant="link"
          class="new-folder-icon"
          @click="renderNewFolderForm"
          v-b-tooltip
          title="Create Folder"
          placement="bottom"
        >
          <fa-icon :icon="['fas', 'plus']" size="lg" />
        </b-button>
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

        <!--
        <h4>Invites</h4>
        <ul v-if="shareForm.invitees.length">
          <li v-for="(i) in shareForm.invitees">{{ i }}</li>
        </ul>
        -->

      </aside>

      <main class="col-md-9">
        <b-row v-if="isCreatePostFormVisible" class="mb-3">
          <b-col>
            <CreatePost :session_user="session_user" :timeline="timeline" :data="createPostData" :onHide="hideCreatePost" />
          </b-col>
        </b-row>
        <!-- +++ File Thumbnails / Dropzone File Uploader +++ -->
        <b-row>
          <b-col>
            <vue-dropzone 
              ref="myVueDropzone" 
              id="vaultfile-dropzone"
              :options="dropzoneOptions"
              v-on:vdropzone-sending="sendingEvent"
              v-on:vdropzone-success="successEvent"
              :useCustomSlot="true"
            >
              <div class="dropzone-custom-content">
                <fa-icon :icon="['fas', 'upload']" size="lg" />
                <span>Drop files here to upload, or click to browse.</span>
              </div>
            </vue-dropzone>
          </b-col>
        </b-row>
        
        <!-- Audio Recorder -->
        <b-row v-if="showAudioRec">
          <b-col>
            <AudioRecorder
              @close="showAudioRec=false;"
              @complete="file => recordCompleted(file, true)"
            />
          </b-col>
        </b-row>

        <!-- +++ Minor Nav +++ -->
        <b-row class="py-3">

          <b-col>
            <section class="d-md-flex justify-content-between align-items-center">

              <b-breadcrumb class="pl-0 my-0">
                <b-breadcrumb-item v-for="(bc, index) in breadcrumbNav" :key="bc.pkid" @click="doNav(bc.pkid)" :active="bc.active">{{ bc.text }}</b-breadcrumb-item>
              </b-breadcrumb>
        
              <div v-if="this.selectedMediafiles.length" class="d-flex align-items-center">
                <div class="mr-3">{{ this.selectedMediafiles.length }} selected</div>
                <div class="mr-5" v-if="isAllSelected"><b-button @click="clearSelected()" variant="light">Clear Selection</b-button></div>
                <div class="mr-5" v-else><b-button @click="selectAll()" variant="secondary">Select All</b-button></div>
                <div class="">
                  <b-button @click="renderSendForm()" variant="primary" class="mr-1">Send To</b-button>
                  <b-button @click="renderShareForm()" variant="primary" class="mr-1">Share</b-button>
                  <b-button @click="renderDownloadForm()" variant="primary" class="mr-1" :disabled="isDownloading" >
                    <b-spinner small v-if="isDownloading"></b-spinner>
                    Download
                  </b-button>
                  <b-button @click="renderDeleteForm()" variant="danger" class="mr-1">Delete</b-button>
                </div>
              </div>

              <div v-else>
                <b-button variant="link" class="" @click="recordVideo">
                  <fa-icon :icon="['fas', 'video']" size="lg" />
                </b-button>
                <b-button variant="link" class="" @click="recordAudio">
                  <fa-icon :icon="['fas', 'microphone']" size="lg" />
                </b-button>
              </div>

            </section>
          </b-col>
        </b-row>

        <!-- +++ List/Grid Display of Files & Folders +++ -->
        <b-overlay :show="fileUploading" spinner-variant="primary" rounded="sm">
          <b-row :no-gutters="false">

            <!-- Files -->
            <b-col cols="12" md="3" v-for="(mf) in mediafiles" :key="mf.id" role="button" class="mb-3">
              <PreviewFile 
                :data-mf_id="mf.id" 
                :mediafile="mf" 
                @input="onPreviewFileInput" 
                @render-lightbox="renderLightbox" 
                class="OFF-p-1 tag-file" 
              />
              <!-- <p class="text-center truncate m-0">{{ mf.mfname }}</p> -->
            </b-col>

            <!-- Vaultfolders -->
            <b-col v-for="(vf) in children" :key="vf.id" cols="12" md="3" class="mb-2">
              <div v-if="vf.is_pending_approval" class="tag-folder img-box tag-shared tag-pending-approval">
                <b-img-lazy fluid @click="renderApproveSharedModal(vf)" src="/images/icons/folder-icon.png" class="folder d-block mx-auto" role="button" :alt="`Folder ${vf.slug}`"></b-img-lazy>
                <div class="icon-pending-approval" style="font-size: 3rem;">
                  <fa-icon :icon="['fas', 'share-alt']" class="text-primary" />
                </div>
              </div>
              <div v-else class="tag-folder img-box">
                <b-img fluid @click="doNav(vf.id)" src="/images/icons/folder-icon.png" class="folder d-block mx-auto" role="button" :alt="`Folder ${vf.slug}`"></b-img>
                <div class="file-count">
                  <b-badge variant="warning" class="p-2">{{ vf.mediafiles.length + vf.vfchildren.length }}</b-badge>
                </div>
                <div @click="renderDeleteFolderForm(vf)" class="clickme_to-delete" role="button">
                  <fa-icon :icon="['fas', 'trash']" size="lg" class="text-danger" />
                </div>
              </div>
              <p class="text-center truncate m-0 tag-folder-title">{{ vf.name }}</p>
            </b-col>
          </b-row>
        </b-overlay>

      </main>

    </b-row>

    <!-- Modal for sharing selected files or folders to one or more users (ie manager -> creators) -->
    <b-modal v-model="isShareFilesModalVisible" size="md" title="Share Files" >
      <b-form>

          <div class="autosuggest-container">
              <!--
            <b-form-group>
              <b-form-input
                v-model="inviteeInput"
                v-on:keydown.enter.prevent="addInvite"
                type="text"
                placeholder="Enter names of creators you manage..."
              ></b-form-input>
            </b-form-group>
              -->
            <b-form-group class="m-0">
              <vue-autosuggest
                v-model="query"
                :suggestions="filteredOptions"
                @focus="focusMe"
                @input="getMatches"
                @selected="addSharee"
                :get-suggestion-value="getSuggestionValue"
                :input-props="{id:'autosuggest__input', class: 'form-control', placeholder:'Enter a username to share with'}">
                <div slot-scope="{suggestion}" style="display: flex; align-items: center;">
                  <div style="{ display: 'flex' }">{{suggestion.item.label}}</div>
                </div>
              </vue-autosuggest>
            </b-form-group>
          </div>

          <div class="share-list mt-3" v-if="shareForm.sharees.length">
            <div>
              <span v-for="(se, idx) in shareForm.sharees" :key="idx" class="tag-sharee mr-2">
                <b-badge variant="info" class="p-2">{{ se.label }} 
                  <span @click="removeSharee(se)" role="button"><fa-icon :icon="['far', 'times']" /></span>
                </b-badge>
              </span>
            </div>
          </div>

          <!--
          <b-button type="submit" variant="primary">Share Selected</b-button>
          < b-button @click="cancelShareFiles" type="cancel" variant="secondary">Cancel</b-button>
          -->
        </b-form>

          <template #modal-footer>
            <div class="w-100 text-right">
              <b-button variant="secondary" @click="hideShareForm">Cancel</b-button>
              <b-button variant="primary" @click="shareSelectedFiles">Share</b-button>
            </div>
          </template>

    </b-modal>


    <!-- Modal for selecting where to 'send' the selected files: to the story (max 1 file), to a post, or to a message -->
    <b-modal v-model="isSendFilesModalVisible" id="send-files-modal" size="sm" title="Send To" hide-footer body-class="p-0" >
      <div>
        <b-list-group>
          <b-list-group-item v-if="sendChannels.includes('post')">
            <b-button @click="sendSelected('post')" variant="link" class="send-to-group-item text-decoration-none pr-3">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mr-2" size="lg" />
              Send in New Post
            </b-button>
          </b-list-group-item>
          <b-list-group-item v-if="sendChannels.includes('story')">
            <b-button :disabled="selectedMediafiles.length>1" @click="sendSelected('story')" variant="link" class="send-to-group-item text-decoration-none pr-3">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mr-2" size="lg" />
              Send in New Story
            </b-button>
          </b-list-group-item>
          <b-list-group-item v-if="sendChannels.includes('message')">
            <b-button @click="sendSelected('message')" variant="link" class="send-to-group-item text-decoration-none pr-3">
              <fa-icon :icon="['far', 'plus-square']" fixed-width class="mr-2" size="lg" />
              Send in New Message
            </b-button>
          </b-list-group-item>
        </b-list-group>
      </div>
    </b-modal>

    <!-- Modal for deleting selected files or folders -->
    <b-modal v-model="isDeleteFilesModalVisible" size="md" title="Confirm Delete" >
        <p>Are you sure you want to delete the following {{ selectedMediafiles.length }} files?</p>
        <b-list-group class="delete-list">
          <b-list-group-item v-for="(mf) in selectedMediafiles" :key="mf.id">{{ mf.mfname }}</b-list-group-item>
        </b-list-group>
        <template #modal-footer>
            <b-button variant="secondary" @click="isDeleteFilesModalVisible=false">Cancel</b-button>
            <b-button variant="danger" @click="deleteSelectedFiles">Delete Files</b-button>
        </template>
    </b-modal>

    <!-- Form modal for creating a new sub-folder under the current folder -->
    <b-modal id="modal-create-folder" v-model="isCreateFolderModalVisible" size="lg" title="Create Folder" >
      <b-form @submit.prevent="storeFolder">
          <b-form-group>
            <b-form-input id="folder-name"
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

    <!-- Form modal for deleting a sub-folder and *all* contents  -->
    <b-modal id="modal-delete-folder" v-model="isDeleteFolderModalVisible" size="lg" title="Confirm Delete" >
      <p class="m-0">Are you sure you want to permanently delete this folder and all the files it contains, including subfolders & their contents?</p>
      <template #modal-footer>
        <b-button @click="hideDeleteFolderForm" type="cancel" variant="secondary">Cancel</b-button>
        <b-button @click="deleteFolder" variant="danger">Delete Folder and All Contents</b-button>
      </template>
    </b-modal>

    <!-- 'Lightbox' modal for image preview when clicking on a file in the vault grid/list -->
    <b-modal v-model="isMediaLightboxModalVisible" id="modal-media-lightbox" centered title="" hide-footer body-class="p-0" size="lg">
      <MediaLightbox :session_user="session_user" :mediafile="lightboxSelection" />
    </b-modal>

    <!-- Form modal for image preview before saving to story (%FIXME DRY: see StoryBar.vue) -->
    <b-modal v-model="isSaveToStoryModalVisible" id="modal-save-to-story-form" size="lg" title="Save to Story">
      <section>
        <div class="box-image-preview text-center">
          <b-img-lazy v-if="storyAttrs.selectedMediafile" fluid :src="storyAttrs.selectedMediafile.filepath"></b-img-lazy>
        </div>
      </section>
      <b-form v-on:submit.prevent class="mt-3">
        <b-form-group label='"Swipe Up" Link (optional)' label-for="swipe-up-link">
          <b-form-input id="swipe-up-link" type="url" v-model="storyAttrs.link" :state="urlState" placeholder="http://example.com"></b-form-input>
        </b-form-group>
      </b-form>

      <template #modal-footer>
        <b-button variant="secondary" size="sm" @click="isSaveToStoryModalVisible=false">Cancel</b-button>
        <b-button variant="primary" size="sm" @click="storeStory()">Save</b-button>
      </template>
    </b-modal>

    <!-- 'Lightbox' modal for image preview when clicking on a file in the vault grid/list -->
    <b-modal v-model="isApproveSharedModalVisible" title="Approve Shared Vault Files" size="xl">
      <p>The user {{ strSharerName }} has shared {{ strSharedMfCount }} files with you...</p>
      <template #modal-footer>
        <div class="w-100">
          <b-button variant="danger" @click="declineShared(selectedVfToApprove)">Reject</b-button>
          <b-button variant="warning" @click="approveShared(selectedVfToApprove)">Accept</b-button>
        </div>
      </template #modal-footer>
    </b-modal>

    <!-- Video Recorder -->
    <VideoRecorder v-if="showVideoRec" @close="showVideoRec=false;" @complete="recordCompleted" />

    <!-- Modal for downloading selected files or folders -->
    <b-modal v-model="isDownloadFilesModalVisible" size="md" title="Confirm Download" no-close-on-backdrop>
        <p>Are you sure you want to download the following {{ selectedMediafiles.length }} files?</p>
        <b-list-group class="download-list">
          <b-list-group-item v-for="(mf) in selectedMediafiles" :key="mf.id">{{ mf.mfname }}</b-list-group-item>
        </b-list-group>
        <template #modal-footer>
            <b-button variant="secondary" @click="isDownloadFilesModalVisible=false">Cancel</b-button>
            <b-button variant="primary" :disabled="isDownloading" @click="downloadSelectedFiles">
              <b-spinner small v-if="isDownloading"></b-spinner>
              Download Files
            </b-button>
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
import MediaLightbox from '@components/vault/MediaLightbox'
import TreeItem from '@components/vault/TreeItem'
import VideoRecorder from '@components/videoRecorder'
import AudioRecorder from '@components/audioRecorder'
import CreatePost from '@components/common/CreatePost.vue';

export default {

  props: {
    session_user: null,
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
    ...Vuex.mapState(['timeline']),

    isLoading() {
      return !this.vault_pkid || !this.vaultfolder_pkid || !this.vault || !this.vaultfolder || !this.foldertree || !this.session_user
    },

    strSharerName() { // format string for sharer name
      return this.selectedVfToApprove?.cattrs?.shared_by?.username || 'Unknown'
    },

    strSharedMfCount() { // format string for number of files shared
      //return this.selectedVfToApprove?.mediafile_count || '--'
      return this.selectedVfToApprove?.mediafilesharelogs?.length || '--'
    },

    parent() {
      return this.vaultfolder.vfparent
    },

    children() { // ie, child folders (not files)
      return this.vaultfolder.vfchildren
    },

    breadcrumbNav() {
      const result = []
      for ( let b of this.breadcrumb ) {
        const isActive = b.pkid === this.currentFolderId
        result.push({
          pkid: b.pkid,
          text: b.is_root ? 'Home' : b.vfname,
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

    selectedMediafiles() { // selected via checkbox
      return _.filter(this.mediafiles, o => (o.selected))
    },

    urlState() { // for story form
      return this.storyAttrs.link ? validateUrl(this.storyAttrs.link) : null
    },

  },

  data: () => ({

    vault: null,
    foldertree: null, // the nav tree data structure
    mediafiles: {}, // %FIXME: use array not keyed object!

    // By default we can send to story, post, or message...may be overridden if this 'page' is 
    // loaded in another context (hack for mvp)
    sendChannels: ['story', 'post', 'message'],
    //sendAction: null,

    isSendFilesModalVisible: false,
    isShareFilesModalVisible: false,
    isDeleteFilesModalVisible: false,
    isCreateFolderModalVisible: false,
    isDeleteFolderModalVisible: false,
    isSaveToStoryModalVisible: false,
    isMediaLightboxModalVisible: false,
    isApproveSharedModalVisible: false,
    isDownloadFilesModalVisible: false,
    isCreatePostFormVisible: false,

    selectedVfToApprove: null,
    selectedVfToDelete: null,

    lightboxSelection: null,

    createPostData: null,

    createForm: {
      name: '',
      //vault_id: this.vault_pkid,
      //parent_id: this.currentFolderId,
    },

    storyAttrs: { // for 'send-to-story' form
      color: '#fff',
      contents: '',
      link: null,
      selectedMediafile: null, // if selected from vault
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
      maxFilesize: 5000, // 5 GB
      headers: { 
        'X-Requested-With': 'XMLHttpRequest', 
        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
      },
      dictDefaultMessage: 'Drop files here to upload, or click to browse.',
    },

    showVideoRec: false,
    fileUploading: false,
    showAudioRec: false,
    isAllSelected: false,
    isDownloading: false,
  }), // data

  methods: {

    // from Vue tree demo (TreeItem aka VaultNavigation)
    makeFolder() { // %FIXME ?? redudant?
    },
    addItem() {
    },

    hideCreatePost() {
      this.isCreatePostFormVisible = false
    },

    sendSelected(resourceType) {
      // send (share) selected files to a post, story, or message
      // %TODO: as part of AF-492 deprecate this code 20210806 -- No actually we need it to send from vault to other areas (eg post create form)
      const params = {
          mediafile_ids: this.selectedMediafiles.map( ({id}) => id ),
      }
      console.log('Vault/Dashboard::sendSelected', {
        resourceType, 
        params,
      })

      switch (resourceType) {
        case 'story':
          /*
          params.context = 'send-selected-mediafiles-to-story' // 'mediafiles-selected-in-vault'
          if ( this.sendAction === 'storybar' ) {
            this.$router.replace({ name: 'index', params })
          } 
          */
          if ( this.selectedMediafiles.length ) {
            //this.$router.replace({ name: 'index', params })
            this.storyAttrs.link = null
            this.storyAttrs.selectedMediafile = this.selectedMediafiles[0] // if selected from vault
            this.isSaveToStoryModalVisible = true
          }
          break
        case 'post':
          params.context = 'send-selected-mediafiles-to-post' // 'mediafiles-selected-in-vault'
          this.createPostData = params
          this.isCreatePostFormVisible = true
          this.isSendFilesModalVisible = false
          break
        case 'message':
          params.context = 'send-selected-mediafiles-to-message' // 'mediafiles-selected-in-vault'
          this.$router.replace({ name: 'chatthreads.create', params })
          break
      }
      this.sendChannels =  ['story', 'post', 'message']
      //this.sendAction =  null
    },

    // API to update a new story in the database for this user's timeline
    // %NOTE: can only send 1 file per new story 
    async storeStory() {
      const stype = 'image'

      let payload = new FormData()
      payload.append('stype', stype) // %FIXME: hardcoded
      payload.append('bgcolor', this.storyAttrs.color || "#fff")
      payload.append('content', this.storyAttrs.contents)
      if ( this.storyAttrs.link ) {
        payload.append('link', this.storyAttrs.link)
      }

      switch ( stype ) {
        case 'text':
          break
        case 'image':
          if ( this.storyAttrs.selectedMediafile ) {
            payload.append('mediafile_id', this.storyAttrs.selectedMediafile.id)
          }
          break
      } 

      const response = await axios.post( this.$apiRoute('stories.store'), payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      })
      this.isSaveToStoryModalVisible = false
      this.isSendFilesModalVisible = false
      this.resetStoryForm()

      if ( this.$route.params.context === 'storybar' ) {
        //this.$route.params = null %TODO: clear ?
        this.$router.replace({ 
          name: 'index', 
          params: {
            toast: { title: 'New story successfully added!', variant: 'success' },
          },
        })
      }
    },

    resetStoryForm() {
      this.storyAttrs.selectedMediafile = null
      this.storyAttrs.color = '#fff'
      this.storyAttrs.contents = ''
      this.storyAttrs.link = null
    },

    renderLightbox(mediafile) {
      this.lightboxSelection = mediafile
      this.isMediaLightboxModalVisible = true
    },

    renderShareForm() {
      this.isShareFilesModalVisible = true
    },
    hideShareForm() {
      this.isShareFilesModalVisible = false
      this.shareForm.sharees = []
    },

    renderSendForm() {
      this.isSendFilesModalVisible = true
    },

    renderDeleteForm() {
      this.isDeleteFilesModalVisible = true
    },

    renderDownloadForm() {
      this.isDownloadFilesModalVisible = true
    },

    onPreviewFileInput(value) {
      Vue.set(this.mediafiles, value.id, value) // Sets .selected on mediafiles array depending on child form component's action
    },

    selectAll() {
      this.isAllSelected = true;
      this.mediafiles = _.mapValues( this.mediafiles, o => ({ ...o, selected: true }) )
    },
    clearSelected() {
      this.isAllSelected = false;
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

    // Share selected files to other user(s)...injects selected files into receipent's vault in a new subfolder
    async shareSelectedFiles(e) {
      // this.selectedMediafiles
      /*
      const response = await axios.patch(`/vaults/${this.vault_pkid}/update-shares`, {
        shareables: this.shareForm.selectedToShare,
        sharees: this.shareForm.sharees.map( o => { return { sharee_id: o.id } }),
        invitees: this.shareForm.invitees.map( o => { return { email: o } }),
      })
       */
      const payload = { 
        user_ids: this.shareForm.sharees.map( o => o.id ),
        mediafile_ids: this.selectedMediafiles.map( o => o.id ),
      }
      const response = await axios.post( this.$apiRoute('vaultfolders.storeByShare'), payload )
      this.isShareFilesModalVisible = false
      this.cancelShareFiles()
      this.clearSelected()
      this.$root.$bvToast.toast( `Successfully shared ${payload.mediafile_ids.length} files)`, {toaster: 'b-toaster-top-center', variant: 'success'} )
    },

    cancelShareFiles() {
      //this.isShareMode = false
      this.shareForm.sharees = []
      this.shareForm.invitees = []
      this.shareForm.selectedToShare = []
      this.query = ''
      this.inviteeInput = ''
    },

    async deleteSelectedFiles(e) {
      const payload = { 
        mediafile_ids: this.selectedMediafiles.map( o => o.id ),
      }
      const response = await axios.post( this.$apiRoute('mediafiles.batchDestroy'), payload )
      this.isDeleteFilesModalVisible = false
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
      this.clearSelected()
      this.$root.$bvToast.toast( `Successfully deleted ${payload.mediafile_ids.length} files)`, {toaster: 'b-toaster-top-center', variant: 'success'} )
    },


    downloadSelectedFiles() {
      const promises = [];
      this.isDownloading = true;
      this.selectedMediafiles.forEach(file => {
        const p = this.axios
          .get(route('mediafiles.show', file.id), { responseType: 'blob' })
          .then(response => {
              const blob = new Blob([response.data])
              const link = document.createElement('a')
              link.href = URL.createObjectURL(blob)
              link.download = file.mfname
              link.click()
              URL.revokeObjectURL(link.href)
            }).catch(console.error)
        promises.push(p)
      });

      Promise.all(promises).then((values) => {
        this.isDownloading = false
        this.isDownloadFilesModalVisible = false
        this.clearSelected()
        this.$root.$bvToast.toast( `Successfully downloaded ${this.selectedMediafiles.length} files)`, {toaster: 'b-toaster-top-center', variant: 'success'} )
      })
    },

    // --- New Vault Folder Form methods ---
    renderNewFolderForm() {
      this.isCreateFolderModalVisible = true
    },

    renderDeleteFolderForm(vf) {
      // %TODO: make an api call to get a *recursive* count of all files & folders contained under the folder to be deleted!!
      this.selectedVfToDelete = vf
      this.isDeleteFolderModalVisible = true
    },
    hideDeleteFolderForm(vf) {
      this.selectedVfToDelete = null
      this.isDeleteFolderModalVisible = false
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
      const postResponse = await this.axios.post( this.$apiRoute('vaultfolders.store'), payload )

      // refresh
      const showResponse = await this.axios.get( this.$apiRoute('vaults.show', { id: this.vault_pkid }) )
      this.vault = showResponse.data.vault
      this.foldertree = showResponse.data.foldertree || null
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
      this.cancelCreateFolder()
      this.$root.$bvToast.toast('Successfully created folder', {toaster: 'b-toaster-top-center', variant: 'success'})
    },

    async deleteFolder(e) {
      e.preventDefault()
      const deleteResponse = await this.axios.delete( this.$apiRoute('vaultfolders.destroy', { id: this.selectedVfToDelete.id }) )

      // refresh
      const showResponse = await this.axios.get( this.$apiRoute('vaults.show', { id: this.vault_pkid }) )
      this.vault = showResponse.data.vault
      this.foldertree = showResponse.data.foldertree || null
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
      this.selectedVfToDelete = null
      this.isDeleteFolderModalVisible = false
      this.$root.$bvToast.toast( `Deleted folder ( Total of ${deleteResponse.data.number_of_items_deleted} items deleted)`, {toaster: 'b-toaster-top-center', variant: 'success'} )
    },

    //getLink(e, mediafileId) {
    //  axios.get(`/mediafiles/${mediafileId}`).then( (response) => {
    //    console.log('response', { response })
    //  })
    //},

    // for dropzone
    sendingEvent(file, xhr, formData) {
      this.fileUploading = true
      formData.append('resource_id', this.currentFolderId)
      formData.append('resource_type', 'vaultfolders')
      formData.append('mftype', 'vault')
    },

    // for dropzone
    successEvent(file, response) {
      this.fileUploading = false
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
    },

    // Preload the mediafiles in the current folder (pwd)
    async doNav(vaultfolderId) {
      this.currentFolderId = vaultfolderId
      this.$store.dispatch('getVaultfolder', vaultfolderId)
    },

    // ---

    addSharee(sharee) {
      //console.log('addSharee', { sharee, })
      if ( !this.shareForm.sharees.some(o => o.id === sharee.item.id) ) { // check not already added
        this.shareForm.sharees.push(sharee.item)
      }
      //  this.shareForm.sharees.push(sharee.item)
      this.query = ''
      this.suggestions = []
    },

    removeSharee(sharee) {
      this.shareForm.sharees = this.shareForm.sharees.filter( o => o.id !== sharee.id )
    },

    addInvite(e) {
      const email = e.target.value
      if ( email && !this.shareForm.invitees.includes(email) ) {
        this.shareForm.invitees.push(email)
      }
      this.inviteeInput = ''
    },

    async getMatches(text) {
      const params = {
        term: text,
        field: 'username',
        //field: 'name',
      }
      const response = await axios.get( this.$apiRoute('users.match'), { params } )
      this.suggestions = response.data.filter( o => o.id !== this.session_user.id ) // exclude session user
    },

    // This is what the <input/> value is set to when you are selecting a suggestion
    getSuggestionValue(suggestion) {
      return suggestion.item.label
    },

    focusMe(e) {
    },

    async renderApproveSharedModal(vf) {
      // %TODO: call to get full details
      const response = await this.axios.get(route('vaultfolders.show', { id: vf.id }))
      this.isApproveSharedModalVisible = true
      this.selectedVfToApprove = response.data.vaultfolder
    },

    async approveShared(vf) {
      //const response = await this.axios.patch( route('vaultfolders.update', { id: vf.id }), { is_pending_approval: 0 })
      const response = await this.axios.post( route('vaultfolders.approveShare', { id: vf.id }) )
      this.$store.dispatch( 'getVaultfolder', this.currentFolderId )
      this.selectedVfToApprove = null // selection
      this.isApproveSharedModalVisible = false
    },

    async declineShared(vf) {
      // soft delete
      //response = await this.axios.patch( route('vaultfolders.delete', { id: vf.id }) )
      const response = await this.axios.post( route('vaultfolders.declineShare', { id: vf.id }) )
      this.$store.dispatch('getVaultfolder', this.currentFolderId)
      this.selectedVfToApprove = null // selection
      this.isApproveSharedModalVisible = false
    },

    recordVideo() { // %TODO
      this.showVideoRec = true
    },

    recordAudio() {
      this.showAudioRec = true
    },

    async recordCompleted(file, is_audio) {
      if (is_audio) {
        this.showAudioRec = false;
      } else {
        this.showVideoRec = false;
      }
      this.fileUploading = true;
      let payload = new FormData();
      payload.append('resource_id', this.currentFolderId);
      payload.append('resource_type', 'vaultfolders');
      payload.append('mftype', 'vault');
      payload.append('mediafile', file);
      payload.append('mfname', `${is_audio ? 'audio' : 'video'}-${new Date().valueOf()}.webm`);
      const response = await this.axios.post(route('mediafiles.index'), payload, {
        headers: { 
          'X-Requested-With': 'XMLHttpRequest', 
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
      });
      this.$store.dispatch('getVaultfolder', this.currentFolderId);
      this.fileUploading = false;
    },
  },

  created() {
    this.currentFolderId = this.vaultfolder_pkid
    //this.$store.dispatch('getVault', this.vault_pkid)
    this.axios.get(route('vaults.show', { id: this.vault_pkid })).then((response) => {
      this.vault = response.data.vault
      this.foldertree = response.data.foldertree || null
      this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid)
    })

      /*
    // %HERE %FIXME act on any special context params passed from Vue router
    if ( this.$route.params.context ) {
      switch( this.$route.params.context ) {
        case 'storybar': // we got here from the storybar, so instead of sending the story directly, return to story bar (!)
          this.sendChannels = ['story']
          this.sendAction = 'storybar'
          break
      }
    }
    */
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
    MediaLightbox,
    VideoRecorder,
    AudioRecorder,
    CreatePost,
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

<style lang="scss" >
body {
    .autosuggest__results ul {
      margin-top: 2rem;
      list-style: none !important;
    }
  .img-box {
    padding-top: calc(85% - 2px);
    img {
      object-fit: cover;
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
    .tag-selected {
      filter: brightness(50%);
    }
    .render-date {
      position: absolute;
      top: 5px;
      left: 5px;
      border-radius: 5px;
      background: #535353;
      opacity: 0.7;
      padding: 0.3rem;
      p {
        opacity: 1;
        color: #fff;
        font-size: 11px;
      }
    }
    .select-file {
      position: absolute;
      top: 0;
      right: 0;
    }
  }
}
</style>

<style lang="scss" scoped>
body {
  .vault-container .breadcrumb {
    background-color: #fff;
    border-radius: 0;
  }
  .vault-container .breadcrumb .breadcrumb-item {
    font-size: 1.2rem;
  }
  .vault-container .breadcrumb .breadcrumb-item.active {
    color: #212529;
  }
  ul.folder-nav {
    list-style: none;
  }
  .tag-shared {
    background-color: pink;
  }

  .vault-container {
    background: #fff;
    .tag-folder {
      //border: solid #b5b5bf 3px;
      background: #f5f5f5;
      border-radius: 5px;
    }
    .tag-folder.tag-pending-approval {
      border: solid orange 3px;
    }
    .tag-folder .file-count {
      position: absolute;
      top: 0.7rem;
      left: 1.5rem;

      .badge {
        color: #fff;
        background: #535353;
        opacity: 0.7;
        font-size: 11px;
      }
    }
    .tag-folder .clickme_to-delete {
      position: absolute;
      top: 0.7rem;
      right: 1.5rem;
    }
    .tag-folder .icon-pending-approval {
      position: absolute;
      top: 45%;
      right: 50%;
    }

    .tag-folder-title {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      text-transform: capitalize;
      color: #fff;
      padding: 2px 12px;
      border-radius: 5px;
      background: rgba(83, 83, 83, 0.7);
    }
  }

  .vue-dropzone {
    background: #d3d3d370;
    border: 1px dashed gray;
    align-items: center;
    display: flex;
    justify-content: center;
  }
  .share-list .tag-sharee {
    font-size: 1.2rem;
  }

  .truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
   }
  .tag-file .truncate {
    width: 200px;
  }
  .tag-folder .truncate {
    width: 200px;
  }
}

.new-folder-icon {
  position: absolute; 
  top: -6px;
  right: 15px;
  padding: 4px 6px;
}
</style>

<style>
#send-files-modal .modal-dialog {
  width: 248px;
}
</style>
