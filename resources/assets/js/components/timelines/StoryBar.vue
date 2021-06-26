<template>
  <div v-if="!isLoading" class="crate tag-crate crate-story_bar row OFF-mb-3 mx-0">
    <section class="d-flex flex-wrap justify-content-start w-100">
      <div class="story">
        <b-form-file @change="handleSelect" ref="fileInput" v-model="selectedFile" class="d-none"></b-form-file>
        <div @click="selectFile">
          <fa-icon class="mt-1" :icon="['far', 'plus-circle']" size="2x" />
        </div>
      </div>
      <div class="ml-3 pr-3 mb-3 story my-story">
        <router-link :to="{ name: 'stories.player', query: { timeline: 'me' } }" class="box-story">
          <b-img rounded="circle" class="p-0" :src="session_user.avatar.filepath" alt="My avatar" />
        </router-link>
      </div>
      <div v-for="tl in timelines" :key="tl.id" class="ml-3 mb-3 story">
        <router-link :to="{ name: 'stories.player' }" class="box-story">
          <b-img rounded="circle" class="p-0" :src="tl.avatar.filepath" alt="Story owner's avatar" />
        </router-link>
      </div>
    </section>

    <b-modal v-model="isSelectFileModalVisible" id="modal-select-file" size="lg" title="Save to Story" body-class="p-0">
      <div>
        <b-img fluid :src="url"></b-img>
      </div>
      <template #modal-footer>
        <div class="w-100">
          <b-button variant="warning" size="sm" class="float-right" @click="isSelectFileModalVisible=false">Cancel</b-button>
          <b-button variant="primary" size="sm" class="float-right" @click="shareStory">Save</b-button>
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
    url: null,
    timelines : null,
    isSelectFileModalVisible: false,
    isLoadedHack: false, // hack to prevent multiple loads due to session_user loads
    selectedFile: null,

    // put inside a form?
    stype: 'text',
    storyAttrs: {
      color: '#fff',
      contents: '',
      link: '',
    },
  }),

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
  },

  methods: {
    selectFile() {
      console.log('selectFile')
      this.$refs.fileInput.$el.childNodes[0].click()
    },

    handleSelect(e) {
      console.log('handleSelect')
      const file = e.target.files[0]
      this.url = URL.createObjectURL(file)
      //this.url = URL.createObjectURL(this.selectedFile)
      //this.$bvModal.show('modal-select-file', { })
      this.isSelectFileModalVisible = true
    },

    async shareStory() {
      //const url = `/${this.dtoUser.username}/stories`;
      let payload = new FormData();
      payload.append('stype', this.stype);
      payload.append('bgcolor', this.storyAttrs.color || null);
      payload.append('content', this.storyAttrs.contents);
      payload.append('link', this.storyAttrs.link);

      switch ( this.stype ) {
        case 'text':
          break;
        case 'image':
          payload.append('mediafile', this.selectedFile);
          break;
      } 

      const response = await axios.post(`/stories`, payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
      this.isSelectFileModalVisible = false
      this.selectedFile = null
    },

    bgColor(story) {
      return Object.keys(story).includes('background-color')
        ? story.customAttributes['background-color']
        : 'yellow'
    },
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
  .my-story {
    border-right: solid 2px #3a3a3a;
  }
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
