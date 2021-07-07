<template>
  <div v-if="!isLoading" class="crate tag-crate crate-story_bar row OFF-mb-3 mx-0">
    <section class="d-flex flex-wrap justify-content-start w-100">

      <!-- Add to story icon form -->
      <div class="story">
        <b-form-file @change="handleDiskSelect" ref="fileInput" v-model="fileInput" class="d-none"></b-form-file>
        <div @click="isSelectFileModalVisible=true">
          <fa-icon class="mt-1" :icon="['far', 'plus-circle']" size="2x" />
        </div>
      </div>

      <!-- Followed creators' stories avatar -->
      <div v-for="tl in timelines" :key="tl.id" class="ml-3 mb-3 story">
        <router-link :to="{ name: 'stories.player', params: { timeline_id: tl.id } }" class="box-story">
          <b-img v-b-popover.hover.top="{variant: 'info', content: tl.slug}" rounded="circle" class="p-0" :src="tl.avatar.filepath" alt="Story owner's avatar" />
        </router-link>
        <!--
        <div>
          <pre>{{ tl.slug }}</pre>
          <pre>{{ JSON.stringify(tl.stories.map( s => ({ id: s.id, slug: s.slug, created: s.created_at }) )[0], null, 2) }}</pre>
        </div>
        -->
      </div>

    </section>

    <!-- Modal for selecting file from disk vs vault -->
    <b-modal v-model="isSelectFileModalVisible" id="modal-select-file" size="lg" title="Select Picture or Video" hide-footer>
      <div>
          <b-button variant="primary" class="" @click="selectFromFiles">Select from Files</b-button>
          <b-button variant="primary" class="" :to="{ name: 'vault.dashboard', params: { context: 'storybar' } }">Select from Vault</b-button>
      </div>
    </b-modal>

    <!-- Form modal for image preview before saving to story -->
    <b-modal v-model="isPreviewModalVisible" id="modal-save-to-story-form" size="lg" title="Save to Story" body-class="p-0">
      <div>
        <b-img fluid :src="selectedDiskfileUrl"></b-img>
      </div>
      <template #modal-footer>
        <div class="w-100">
          <b-button variant="warning" size="sm" @click="isPreviewModalVisible=false">Cancel</b-button>
          <b-button variant="primary" size="sm" @click="storeStory('image')">Save</b-button>
        </div>
      </template>
    </b-modal>

  </div>
</template>

<script>
import Vuex from 'vuex'

export default {
  props: {
    session_user: null,
  },

  computed: {
    //...Vuex.mapState(['stories']),

    isLoading() {
      return !this.session_user || !this.timelines
    },
  },

  data: () => ({
    timelines : null,

    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
    isPreviewModalVisible: false,
    isSelectFileModalVisible: false,

    // Story form input values...
    //   put inside a form JSON??
    fileInput: null, // form input
    //stype: 'text',
    storyAttrs: {
      color: '#fff',
      contents: '',
      link: '',
    },

    selectedDiskfileUrl: null,
  }),

  methods: {
    selectFromFiles() {
      this.$refs.fileInput.$el.childNodes[0].click()
    },
    selectFromVault() {
    },

    handleDiskSelect(e) {
      console.log('handleDiskSelect')
      const file = e.target.files[0]
      this.selectedDiskfileUrl = URL.createObjectURL(file)
      //this.$bvModal.show('modal-select-file', { })
      this.isPreviewModalVisible = true
    },

    // API to create a new story record (ie 'update story timeline') in the database for this user's timeline
    async storeStory(stype) {
      let payload = new FormData()
      payload.append('stype', stype)
      payload.append('bgcolor', this.storyAttrs.color || "#fff")
      payload.append('content', this.storyAttrs.contents)
      payload.append('link', this.storyAttrs.link || null)

      switch ( stype ) {
        case 'text':
          break
        case 'image':
          payload.append('mediafile', this.fileInput)
          break
      } 

      const response = await axios.post(`/stories`, payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      })
      this.isPreviewModalVisible = false
      this.isSelectFileModalVisible = false
      this.fileInput = null // form input

      this.$root.$bvToast.toast('Story successfully uploaded!', {
        toaster: 'b-toaster-top-center',
        title: 'Success',
        variant: 'success',
      })
    },

    bgColor(story) {
      return Object.keys(story).includes('background-color')
        ? story.customAttributes['background-color']
        : 'yellow'
    },
  },

  created() {
    /*
    this.$store.dispatch('getStories', {
      //user_id: this.session_user.id,
      following: 1,
      stypes: 'image', // %FIXME: should be 'photo' (ideally we use PHP ENUM?)
    })
     */
    // %NOTE: we don't really need the stories here, just the timelines that have stories 
    const response = axios.get( this.$apiRoute('timelines.myFollowedStories')).then ( response => {
      this.timelines = response.data.data
    })
    if ( this.$route.params.toast ) {
      this.$root.$bvToast.toast(this.$route.params.toast.title || 'Success', {
        title: 'Stories',
        toaster: 'b-toaster-top-center',
        variant: 'success',
      })
    }
  },

  watch: {
  },

  components: {},
}
</script>

<style lang="scss" scoped>
$size: 40px;
$margin: 16px;
.crate {
  .story {
    margin-left: $margin / 2;
    margin-right: $margin / 2;
    margin-bottom: $margin;
  }

  .b-icon {
    height: $size;
  }

  .box-story img {
    width: $size;
    height: $size;
  }

  .box-story .tag-colorfill {
    width: $size;
    height: $size;
    display: block;
    border-radius: 50%;
  }
}
</style>
