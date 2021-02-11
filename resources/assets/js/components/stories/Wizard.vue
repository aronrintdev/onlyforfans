<template>
  <div class="container-fluid wizard-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Stories</h2>

        <article>
          <b-media no-body>
            <b-media-aside>
              <b-img :src="dtoUser.avatar.filepath" rounded="circle" width="64" height="64" alt="avatar"></b-img>
            </b-media-aside>
            <b-media-body>
              <h5 class="mt-3">{{ dtoUser.fullName }}</h5>
            </b-media-body>
          </b-media>
        </article>

        <hr />

        <div v-if="step===steps.SELECT_TYPE" class="">
          <b-list-group>
            <b-list-group-item v-for="s in stories" :key="s.id" v-bind:style="{ backgroundColor: parseBackgroundColor(s) }">
              <article v-if="s.type==='text'">
                {{ s.content }}
              </article>
              <article v-if="s.type==='photo'" v-bind:class="{ 'tag-image': s.type==='photo' }">
                <b-img fluid :src="s.mf_url" alt="story pic"></b-img>
              </article>
            </b-list-group-item>
          </b-list-group>
        </div>

        <div v-if="step===steps.EDIT || step===steps.PREVIEW" class="step-edit">
          <text-story-form v-if="type==='text'" 
                           v-bind:attrs="storyAttrs"
                           v-on:set-color="setColor($event)"
                           v-on:do-cancel="step=steps.SELECT_TYPE"
                           ></text-story-form>
          <photo-story-form v-if="type==='photo'" 
                            v-bind:attrs="storyAttrs"
                            v-on:do-cancel="step=steps.SELECT_TYPE"
                            ></photo-story-form>
        </div>

      </aside>

      <main class="col-md-9 d-flex align-items-center">
        <div v-if="step===steps.SELECT_TYPE" class="step-select_type mx-auto">
          <section class="row">
            <article class="col-md-6">
              <input ref="fileUpload" type="file" @change="selectMediaFile" hidden>
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
                                      v-if="type==='text'" 
                                      v-bind:attrs="storyAttrs" 
                                      ></text-story-preview>
        </div>

        <div v-if="step===steps.PREVIEW" class="step-preview mx-auto">
          <div id="preview">
            <img v-if="imgPreviewUrl" :src="imgPreviewUrl" class="img-fluid" />
          </div>
        </div>

      </main>

    </section>
  </div>
</template>

<script>
import { eventBus } from '../../app';
import TextStoryForm from './TextStoryForm.vue';
import TextStoryPreview from './TextStoryPreview.vue';
import PhotoStoryForm from './PhotoStoryForm.vue';

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
  },

  data: () => ({

    show: true,

    storyAttrs: {
      contents: '',
      color: '#fff',
    },
    mediaFile: null, // the photo

    type: 'text',

    steps : {
      SELECT_TYPE: 'select-type',
      EDIT: 'edit',
      PREVIEW: 'preview',
    },

    step: null,

    imgPreviewUrl: null,

  }),

  mounted() {
    this.step = this.steps.SELECT_TYPE;
  },

  created() {
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
  },


  methods: {
    async shareStory() {
      //const url = `/${this.dtoUser.username}/stories`;
      let payload = new FormData();
      const json = JSON.stringify({
        type: this.type,
        bgcolor: this.storyAttrs.color || null,
        content: this.storyAttrs.contents,
      });
      payload.append('attrs', json);

      switch ( this.type ) {
        case 'text':
          break;
        case 'photo':
          payload.append('mediaFile', this.mediaFile);
          break;
      } 

      const response = await axios.post(`/stories`, payload, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
      this.step = this.steps.SELECT_TYPE;
      // %TODO: handle error case / catch
    },

    setColor(color) {
      console.log(`Setting color: ${color}`);
      this.storyAttrs.color = color;
    },

    createTextStory(e) {
      this.type = 'text';
      this.step = this.steps.EDIT;
    },

    createPhotoStory(e) {
      this.type = 'photo';
      //this.step = this.steps.EDIT;
      //document.getElementById("fileUpload").click()
      this.$refs.fileUpload.click()
    },

    // https://dev.to/diogoko/file-upload-using-laravel-and-vue-js-the-right-way-1775
    selectMediaFile(event) {
      // `files` is always an array because the file input may be in multiple mode
      const mediaFile = event.target.files[0];
      this.mediaFile = mediaFile;
      this.imgPreviewUrl = URL.createObjectURL(mediaFile);
      this.step = this.steps.PREVIEW;
    },

    parseBackgroundColor(story) {
      if ( story.type==='photo' ) {
        return '#fff';
      } else {
        return story.customAttributes?.['background-color'] || 'yellow';
      }
    },

  },
  components: {
    textStoryForm: TextStoryForm,
    textStoryPreview: TextStoryPreview,
    photoStoryForm: PhotoStoryForm,
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

#preview {
  display: flex;
  justify-content: center;
  align-items: center;
}

#preview img {
  max-width: 100%;
  max-height: 500px;
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

