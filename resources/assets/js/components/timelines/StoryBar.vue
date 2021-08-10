<template>
  <div v-if="!isLoading" class="crate-story_bar mx-0 mb-3">
    <section class="d-flex OFF-flex-wrap justify-content-start w-100 position-relative">

      <!-- Add to story icon form -->
      <div class="story tag-ctrls mr-3">
        <b-form-file @change="handleDiskSelect" ref="fileInput" v-model="fileInput" class="d-none"></b-form-file>
        <div @click="isSelectFileModalVisible=true">
          <fa-icon size="sm" :icon="['far', 'plus']" class="add-story-icon text-white" />
        </div>
      </div>

      <!-- Followed creators' stories avatar -->
      <swiper ref="mySwiper" :options="swiperOptions" class="">
        <swiper-slide v-for="tl in timelines" :key="tl.id" class="story slide tag-followed_timeline">
          <router-link :to="isMyTimeline(tl) ? '' : { name: 'stories.player', params: { timeline_id: tl.id } }" class="box-story" @click.native="isSelectFileModalVisible=true">
            <div class="avatar-container" :class="{ 'my-story-avatar': isMyTimeline(tl) && sessionUserHasActiveStories, 'my-story-avatar-no-story': isMyTimeline(tl) && !sessionUserHasActiveStories, 'all-viewed': tl.allViewed }">
              <b-img
                rounded="circle" 
                :src="tl.avatar.filepath"
                alt="Story owner's avatar" 
              />
            </div>
          </router-link>
        </swiper-slide>
      </swiper>

      <!-- DEBUG code
      <div ref="mySwiper" :options="swiperOptions" class="">
        <div v-for="tl in timelines" :key="tl.id" class="story OFF-slide tag-followed_timeline">
          <router-link :to="{ name: 'stories.player', params: { timeline_id: tl.id } }" class="box-story">
            <b-img 
              v-b-popover.hover.top="{variant: 'info', content: tl.slug}" 
              rounded="circle" 
              :src="tl.avatar.filepath" 
              :class="{ 'my-story-avatar': isMyTimeline(tl) }"
              class="p-0" 
              alt="Story owner's avatar" 
            />
          </router-link>
          <div>
            <pre>{{ tl.slug }}</pre>
            <pre>{{ JSON.stringify(tl.storyqueues.map( sq => ({ timeline_id: tl.id, story_id: sq.story_id, created: sq.created_at }) ), null, 2) }}</pre>
          </div>
        </div>
      </div>
      -->

    </section>

    <!-- Modal for selecting file from disk vs vault -->
    <b-modal v-model="isSelectFileModalVisible" id="modal-select-file" size="md" title="Select a Story Type" hide-footer >
      <div>
        <b-button block variant="primary" class="" @click="selectFromFiles">Add from Device</b-button>
        <b-button block variant="primary" class="" :to="{ name: 'vault.dashboard', params: { context: 'storybar' } }">Add from Vault</b-button>
        <b-button block variant="primary" class="" @click="selectTextOnly">Add Text Only</b-button>
        <hr />
        <b-button block variant="primary" class="" :to="{ name: 'stories.player', params: { timeline_id: timeline.id } }">View My Stories</b-button>
      </div>
    </b-modal>

    <!-- Form modal for story preview (incl. image) before saving -->
    <b-modal v-model="isPreviewModalVisible" id="modal-save-to-story-form" size="lg" title="Save to Story" body-class="OFF-p-0">
      <section class="OFF-d-flex">
        <div class="box-image-preview text-center">
          <b-img v-if="storyAttrs.selectedMediafileId" fluid :src="selectedFileUrl"></b-img>
          <b-img v-else-if="fileInput" fluid :src="selectedFileUrl"></b-img>
        </div>
      </section>
      <b-form v-on:submit.prevent class="mt-3">
        <b-form-group v-if="!storyAttrs.selectedMediafileId && !fileInput" label="Story Text" label-for="story-contents-1">
          <b-form-textarea id="story-contents" v-model="storyAttrs.contents" placeholder="Enter text for your new story..." rows="5" ></b-form-textarea>
        </b-form-group>
        <b-form-group label='"Swipe Up" Link (optional)' label-for="swipe-up-link">
          <b-form-input id="swipe-up-link" type="url" v-model="storyAttrs.link" :state="urlState" placeholder="http://example.com"></b-form-input>
        </b-form-group>
      </b-form>
      <template #modal-footer>
        <b-button variant="secondary" @click="isPreviewModalVisible=false">Cancel</b-button>
        <b-button variant="primary" @click="storeStory()">Save</b-button>
      </template>
    </b-modal>

  </div>
</template>

<script>
import Vuex from 'vuex'
import validateUrl from '@helpers/validateUrl';

export default {
  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    //...Vuex.mapState(['stories']),

    isLoading() {
      return !this.session_user || !this.timeline || !this.timelines
    },

    urlState() {
      return this.storyAttrs.link ? validateUrl(this.storyAttrs.link) : null
    },

    sessionUserHasActiveStories() {
      return !(this.timeline.is_storyqueue_empty && true)
    },

  },

  data: () => ({
    timelines : null,

    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
    isPreviewModalVisible: false,
    isSelectFileModalVisible: false,

    // Story form input values...
    //   put inside a form JSON??
    fileInput: null, // file form input
    storyAttrs: { // for form
      color: '#fff',
      contents: '',
      link: null,
      selectedMediafileId: null, // if selected from vault
    },

    selectedFileUrl: null,

    swiperOptions: {
      slidesPerView: 'auto', // 'auto',
      spaceBetween: 12,
      //direction: 'vertical',
    },
  }),

  methods: {
    selectTextOnly() {
      this.selectedFileUrl = null 
      this.storyAttrs.selectedMediafileId = null
      this.isSelectFileModalVisible = false
      this.isPreviewModalVisible = true
    },

    selectFromFiles() {
      this.$refs.fileInput.$el.childNodes[0].click()
    },

    selectFromVault() {
      // %TODO...right now we are 'hacking' this by redirecting (routing) to vault, selecting files, then routing back here...
    },

    handleDiskSelect(e) {
      const file = e.target.files[0]
      this.selectedFileUrl = URL.createObjectURL(file)
      //this.$bvModal.show('modal-select-file', { })
      this.isSelectFileModalVisible = false
      this.isPreviewModalVisible = true
    },

    resetStoryForm() {
      // reset form input
      this.fileInput = null 
      this.selectedFileUrl = null 
      this.storyAttrs.selectedMediafileId = null
      this.storyAttrs.color = '#fff'
      this.storyAttrs.contents = ''
      this.storyAttrs.link = null
    },

    // API to create a new story record (ie 'update story timeline') in the database for this user's timeline
    async storeStory() {

      // Setup payload for request
      let payload = new FormData()
      payload.append('bgcolor', this.storyAttrs.color || "#fff")
      payload.append('content', this.storyAttrs.contents)
      if ( this.storyAttrs.link ) {
        payload.append('link', this.storyAttrs.link)
      }

      let stype = 'text' // default, if vault or diskfile is attached set to 'image' below
      if ( this.storyAttrs.selectedMediafileId ) {
        payload.append('mediafile_id', this.storyAttrs.selectedMediafileId)
        stype = 'image'
      } else if (this.fileInput) {
        payload.append('mediafile', this.fileInput)
        stype = 'image'
      }
      payload.append('stype', stype)

      // Story POST request
      await axios.post(`/stories`, payload, {
        headers: { 'Content-Type': 'application/json' }
      })
      this.isPreviewModalVisible = false
      this.isSelectFileModalVisible = false
      this.resetStoryForm()

      // update story bar
      const response = await axios.get( this.$apiRoute('timelines.myFollowedStories') )
      this.timelines = response.data.data
      this.arrangeTimelines(this.timelines)

      this.$root.$bvToast.toast('Story successfully uploaded!', {
        toaster: 'b-toaster-top-center',
        title: 'Success',
        variant: 'success',
      })
    },

    bgColor(story) {
      return Object.keys(story).includes('background-color') ? story.customAttributes['background-color'] : 'yellow'
    },

    isMyTimeline(tl) {
      return this.timeline.id === tl.id
    },

    arrangeTimelines(timelines) {
      const index = timelines.findIndex(tl => tl.id === this.timeline.id)
      if (index > -1) {
        timelines.splice(index, 1)
        timelines.unshift({...this.timeline, avatar: this.session_user.avatar })
        this.timelines = timelines
      } else {
        timelines.unshift({...this.timeline, avatar: this.session_user.avatar })
        this.timelines = timelines
      }
    }
  },

  created() {
    // %NOTE: we don't really need the stories here, just the timelines that have stories 
    axios.get( this.$apiRoute('timelines.myFollowedStories')).then ( response => {
      this.timelines = response.data.data
      this.arrangeTimelines(this.timelines)
    })
    if ( this.$route.params.toast ) {
      this.$root.$bvToast.toast(this.$route.params.toast.title || 'Success', {
        title: 'Stories',
        toaster: 'b-toaster-top-center',
        variant: 'success',
      })
    }
  },

  mounted() { 
    if ( this.$route.params.context ) {
      switch( this.$route.params.context ) {
        case 'mediafiles-selected-in-vault': // we got here from the vault, likely with a mediafile to attach to some action
          const mediafileIds = this.$route.params.mediafile_ids || []
          if ( mediafileIds.length ) {
            const response = axios.get(this.$apiRoute('mediafiles.index'), {
              params: {
                mediafile_ids: mediafileIds,
              },
            }).then( response => {
              response.data.data.forEach( mf => {
                this.storyAttrs.selectedMediafileId = mf.id // basically just take the last if multiple
                this.selectedFileUrl = mf.filepath
              })
              this.isSelectFileModalVisible = false
              this.isPreviewModalVisible = true
            })
          }
          //this.sendChannels = ['story']
          //this.sendAction = 'storybar'
          break
      }
    }
  },

  watch: {
    isPreviewModalVisible(v) {
      console.log(`isPreviewModalVisible() ${v}`)
      if (!v) {
        this.resetStoryForm()
      }
    },
    isSelectFileModalVisible(v) {
      console.log(`isSelectFileModalVisible() ${v}`)
      if (v) {
        this.resetStoryForm()
      }
    },
  },

  components: {},
}
</script>

<style lang="scss" scoped>
body .crate-story_bar {

  .story.tag-ctrls {
    position: absolute;
    background: #0195f7;
    width: 20px;
    height: 20px;
    border: solid 2px white;
    border-radius: 100%;
    left: 27px;
    top: 22px;
    z-index: 10;
    text-align: center;
    cursor: pointer;
    .add-story-icon {
      vertical-align: 2px;
    }
  }

  .story.slide {
    position:relative;
    width: 42px !important;
    height: 42px !important;
  }

  .avatar-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 100%;
    padding: 2px;
    background: linear-gradient(45deg,pink,blue);
    -webkit-mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
  }

  .box-story img {
    margin: 1px;
    width: 40px;
    height: 40px;
  }
  .avatar-container.all-viewed::before {
    background: #D3D3D3;
  }
  .avatar-container.my-story-avatar::before {
    background: cyan;
  }
  .avatar-container.my-story-avatar-no-story::before {
    background: none;
  }

}
</style>

<style lang="scss">
body {
  .crate-story_bar .swiper-container {
    margin-left: 0;
    margin-right: 0;
  }

  .box-image-preview img {
    height: 450px;
  }
}
</style>
