<template>
  <div v-if="!isLoading" class="container-fluid wizard-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Stories</h2>

        <article>
          <b-media no-body>
            <b-media-aside>
              <b-img-lazy :src="dtoUser.avatar.filepath" rounded="circle" width="64" height="64" alt="avatar"></b-img-lazy>
            </b-media-aside>
            <b-media-body>
              <h5 class="mt-3">{{ dtoUser.fullName }}</h5>
            </b-media-body>
          </b-media>
        </article>

        <hr />

        <div v-if="step===steps.SELECT_STYPE" class="">
          <b-list-group>
            <b-list-group-item v-for="s in stories" :key="s.id" v-bind:style="{ backgroundColor: parseBackgroundColor(s) }">
              <article v-if="s.stype==='text'">
                {{ s.content }}
              </article>
              <article v-if="s.stype==='image'" v-bind:class="{ 'tag-image': s.stype==='image' }">
                <b-img-lazy fluid :src="s.mf_url" alt="story pic"></b-img-lazy>
              </article>
            </b-list-group-item>
          </b-list-group>
        </div>

        <div v-if="step===steps.EDIT || step===steps.PREVIEW" class="step-edit">
          <text-story-form v-if="stype==='text'" 
            v-bind:attrs="storyAttrs"
            v-on:set-color="setColor($event)"
            v-on:do-cancel="step=steps.SELECT_STYPE"
          ></text-story-form>
          <photo-story-form v-if="stype==='image'" 
            v-bind:attrs="storyAttrs"
            v-on:do-cancel="step=steps.SELECT_STYPE"
          ></photo-story-form>
        </div>

      </aside>

      <main class="col-md-9">
        <div class="d-flex OFF-align-items-center">
          <div v-if="step===steps.SELECT_STYPE" class="step-select_stype mx-auto">
            <section class="row">
              <article class="col-md-6">
                <input ref="fileUpload" type="file" @change="selectMediafile" hidden>
                <div @click="createPhotoStory()" class="clickme_to-create tag-photo tag-bg-cyan text-center d-flex">
                  <div class="align-self-center">
                    <b-icon icon="camera" font-scale="4"></b-icon>
                    <h6 class="mt-1">Create a Photo Story</h6>
                  </div>
                </div>
              </article>
              <article class="col-md-6">
                <div @click="createTextStory()" class="clickme_to-create tag-text tag-bg-pink text-center d-flex">
                  <div class="align-self-center">
                    <b-icon icon="type" font-scale="4"></b-icon>
                    <h6>Create a Text Story</h6>
                  </div>
                </div>
              </article>
            </section>
          </div>

          <div v-if="step===steps.EDIT" class="step-edit w-100">
            <text-story-preview 
              v-if="stype==='text'" 
              v-bind:attrs="storyAttrs" 
            ></text-story-preview>
          </div>

          <div v-if="step===steps.PREVIEW" class="step-preview mx-auto">
            <photo-story-preview v-if="imgPreviewUrl" :imgPreviewUrl="imgPreviewUrl" :attrs="storyAttrs"></photo-story-preview>
          </div>
        </div>

        <div>
          <b-button variant="primary" class="" @click="selectFromVault">Select from Vault</b-button>
        </div>

        <b-modal
          id="modal-select-vaultfile"
          size="lg"
          title="Vault Files"
          hide-footer
          body-class="p-0"
        >
          <b-row>
            <b-col>
              Vault files
            </b-col>
          </b-row>

          <b-row>
            <b-col v-for="mf in mediafiles" :key="mf.id" col-md="3">
              <ImageDisplay :mediafile="mf" :session_user="session_user" :use_mid="true" />
            </b-col>
          </b-row>

        </b-modal>

      </main>

    </section>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import TextStoryForm from '@components/stories/TextStoryForm.vue';
import TextStoryPreview from '@components/stories/TextStoryPreview.vue';
import PhotoStoryForm from '@components/stories/PhotoStoryForm.vue';
import PhotoStoryPreview from '@components/stories/PhotoStoryPreview.vue';
import ImageDisplay from '@components/timelines/elements/ImageDisplay'

export default {

  props: {
    dtoUser: {
      type: Object,
      required: true
    },
    stories: {
      type: Array,
      required: true
    },
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      return !this.session_user || !this.stories || !this.dtoUser
    },
  },

  data: () => ({

    show: true,
    mediafiles: [],

    storyAttrs: {
      color: '#fff',
      contents: '',
      link: '',
    },
    mediafile: null, // the photo

    stype: 'text',

    steps : {
      SELECT_STYPE: 'select-stype',
      EDIT: 'edit',
      PREVIEW: 'preview',
    },

    step: null,

    imgPreviewUrl: null,

  }),

  mounted() {
    this.step = this.steps.SELECT_STYPE;
  },

  created() {
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
  },


  methods: {
    async selectFromVault() {
      const params = {
        mftype: 'vault',
      }
      const response = await axios.get( this.$apiRoute('mediafiles.index'), { params } )
      this.mediafiles = response.data.data
      this.$bvModal.show('modal-select-vaultfile')
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
          payload.append('mediafile', this.mediafile);
          break;
      } 

      const response = await axios.post(`/stories`, payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
      this.step = this.steps.SELECT_STYPE;
      // %TODO: handle error case / catch
    },

    setColor(color) {
      console.log(`Setting color: ${color}`);
      this.storyAttrs.color = color;
    },

    createTextStory(e) {
      this.stype = 'text';
      this.step = this.steps.EDIT;
    },

    createPhotoStory(e) {
      this.stype = 'image';
      //this.step = this.steps.EDIT;
      //document.getElementById("fileUpload").click()
      this.$refs.fileUpload.click()
    },

    // https://dev.to/diogoko/file-upload-using-laravel-and-vue-js-the-right-way-1775
    selectMediafile(event) {
      // `files` is always an array because the file input may be in multiple mode
      const mediafile = event.target.files[0];
      this.mediafile = mediafile;
      this.imgPreviewUrl = URL.createObjectURL(mediafile);
      this.step = this.steps.PREVIEW;
    },

    parseBackgroundColor(story) {
      if ( story.stype==='image' ) {
        return '#fff';
      } else {
        return story.customAttributes?.['background-color'] || 'yellow';
      }
    },

  },
  components: {
    ImageDisplay,
    textStoryForm: TextStoryForm,
    textStoryPreview: TextStoryPreview,
    photoStoryForm: PhotoStoryForm,
    photoStoryPreview: PhotoStoryPreview,
  },
}
</script>

<style scoped>
.wizard-container {
  height: 80vh !important;
}

.clickme_to-create {
  height: 25rem;
  width: 20rem;
  padding: 3rem 5rem;
  color: #fff;
  font-weight: bold;
  border-radius: 0.5rem;
}
.clickme_to-create:hover {
  cursor: pointer;
}

/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) { 
  main {
    background-color: #f3f3f3 !important;
  }
}

body .tag-bg-cyan {
  background-color: cyan;
}
body .tag-bg-gold {
  background-color: gold;
}
body .tag-bg-gray {
  background-color: gray;
}
body .tag-bg-pink {
  background-color: pink;
}

.clickme_to-create.tag-photo {
  background: linear-gradient(#0775c3, #a80bd0);
}
.clickme_to-create.tag-text {
  background: linear-gradient(#0869a4, #e6ce25);
}


aside .list-group > .list-group-item .tag-image,
aside ul > li .tag-image {
  position: relative;
  overflow: hidden;
  border-radius: 50%;
  width: 64px;
  height: 64px;
}
aside .list-group > .list-group-item .tag-image > img,
aside ul > li .tag-image > img{
  width: 64px;
  height: 64px;
}

</style>

