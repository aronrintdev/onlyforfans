<template>
  <div class="container-fluid wizard-container">

    <section class="row h-100">

      <aside class="col-md-3 OFF-tag-debug">

        <h2>My Story</h2>

        <article>
            <b-media no-body>
              <b-media-aside>
                <b-img :src="dtoUser.avatar_url" rounded="circle" width="64" alt="avatar"></b-img>
              </b-media-aside>
              <b-media-body>
                <h5 class="mt-3">{{ dtoUser.fullname }}</h5>
              </b-media-body>
            </b-media>
        </article>

        <hr />

        <div v-if="step===steps.EDIT" class="step-edit">
          <text-story-form v-if="stype==='text'" 
                           v-bind:attrs="textAttrs"
                           v-on:set-color="setColor($event)"
                           v-on:do-cancel="step=steps.SELECT_STYPE"
           ></text-story-form>
          <photo-story-form v-if="stype==='image'"
          ></photo-story-form>
        </div>

      </aside>

      <main class="col-md-9 bg-gray-light OFF-tag-debug d-flex align-items-center">
        <div v-if="step===steps.SELECT_STYPE" class="step-select_stype mx-auto">
          <section class="row">
            <article class="col-md-6">
              <div @click="createPhotoStory()" class="clickme_to-create tag-bg-cyan text-center d-flex">
                <div class="align-self-center">
                   <b-icon icon="camera" font-scale="4"></b-icon>
                  <h6 class="mt-1">Create a Photo Story</h6>
                </div>
              </div>
            </article>
            <article class="col-md-6">
              <div @click="createTextStory()" class="clickme_to-create tag-bg-pink text-center d-flex">
                <div class="align-self-center">
                   <b-icon icon="type" font-scale="4"></b-icon>
                  <h6>Create a Text Story</h6>
                </div>
              </div>
            </article>
          </section>
        </div>

        <div v-if="step===steps.EDIT" class="step-edit">
          <text-story-preview 
                                      v-if="stype==='text'" 
                                      v-bind:attrs="textAttrs" 
                                      username="dtoUser.username"
                                      ></text-story-preview>
        </div>

        <div v-if="step===steps.PREVIEW" class="step-preview">
          <div id="preview">
            <img v-if="imgPreviewUrl" :src="imgPreviewUrl" />
          </div>
        </div>

      </main>

    </section>
  </div>
</template>

<script>
import { eventBus } from '../../app';
import TextStoryForm from './TextStoryForm.vue';
import PhotoStoryForm from './PhotoStoryForm.vue';
import TextStoryPreview from './TextStoryPreview.vue';
import PhotoStoryPreview from './PhotoStoryPreview.vue';

export default {

  props: {
    dtoUser: {
      type: Object,
      required: true
    }
  },

  data: () => ({

    show: true,

    textAttrs: {
      contents: '',
      color: '#fff',
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
    eventBus.$on('select-mediafile', (mediafile) => {
      this.mediafile = mediafile;
      this.imgPreviewUrl = URL.createObjectURL(mediafile);
      this.step = this.steps.PREVIEW;
    });
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
  },


  methods: {
    async shareStory() {
      console.log(`Sending stype ${this.stype}...`);
      const url = `/${this.dtoUser.username}/stories`;
      let payload = new FormData();
      const json = JSON.stringify({
        stype: this.stype,
        bgcolor: this.textAttrs.color,
        content: this.textAttrs.contents,
      });
      payload.append('attrs', json);

      switch ( this.stype ) {
        case 'text':
          break;
        case 'image':
          payload.append('mediafile', this.mediafile);
          break;
      } 
      console.log('shareStory().post', {payload});

      const response = await axios.post(url, payload, {
        headers: {
          // Overwrite Axios's automatically set Content-Type
          'Content-Type': 'application/json',
          //'Accept': 'application/json',
          //'Content-Type': 'multipart/form-data',
        }
      });
      console.log('shareStory().response', {response});
    },

    setColor(color) {
      //$emit('enlarge-text', 0.1)
      console.log(`Setting color: ${color}`);
      this.textAttrs.color = color;
    },

    createTextStory(e) {
      this.stype = 'text';
      this.step = this.steps.EDIT;
    },

    createPhotoStory(e) {
      this.stype = 'image';
      this.step = this.steps.EDIT;
    },

    /*
    setImagePreview(e) {
      const file = e.target.files[0];
      this.imgPreviewUrl = URL.createObjectURL(file);
    },
    */

  },
  components: {
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

aside.tag-debug {
  border: solid 2px pink;
}
main.tag-debug {
  border: solid 2px green;
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

.tag-bg-cyan {
  background: linear-gradient(#0775c3, #a80bd0);
}
.tag-bg-gold {
  background-color: gold;
}
.tag-bg-gray {
  background-color: gray;
}
.tag-bg-pink {
  background: linear-gradient(#0869a4, #e6ce25);
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

</style>

