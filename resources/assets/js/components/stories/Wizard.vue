<template>
  <div class="container-fluid">

    <section class="row">

      <aside class="col-md-3 tag-debug">

        <h2>My Story</h2>

        <hr />

        <div v-if="step===steps.EDIT" class="step-edit">
          <text-story-form 
                                      v-if="stype==='text'" 
                                      v-bind:attrs="textAttrs"
                                      v-on:set-color="setColor($event)"
                                      v-on:do-cancel="step=steps.SELECT_STYPE"
                                      ></text-story-form>
        </div>

      </aside>

      <main class="col-md-9 tag-debug">
        <div v-if="step===steps.SELECT_STYPE" class="step-select_stype">
          <section class="row">
            <article class="col-md-6">
              <div @click="createPhotoStory()" class="clickme_to-create tag-bg-cyan text-center">
                <div class="">
                  <svg width="2.12em" height="2em" viewBox="0 0 17 16" class="bi bi-image" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M14.002 2h-12a1 1 0 0 0-1 1v9l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094L15.002 9.5V3a1 1 0 0 0-1-1zm-12-1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm4 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                </div>
                <h6 class="mt-1">Create a Photo Story</h6>
              </div>
            </article>
            <article class="col-md-6">
              <div @click="createTextStory()" class="clickme_to-create tag-bg-pink text-center">
                <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-type" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2.244 13.081l.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081h1.244zm2.7-7.923L6.34 9.314H3.51l1.4-4.156h.034zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525z"/>
                </svg>
                <h6>Create a Text Story</h6>
              </div>
            </article>
          </section>
        </div>

        <div v-if="step===steps.EDIT" class="step-edit">
          <text-story-preview 
                                      v-if="stype==='text'" 
                                      v-bind:attrs="textAttrs" 
                                      username="username"
                                      ></text-story-preview>
          <photo-story-form v-if="stype==='image'"></photo-story-form>
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

  mounted() {
    this.step = this.steps.SELECT_STYPE;
  },

  created() {
    eventBus.$on('select-mediafile', (mediafile) => {
      this.mediafile = mediafile;
    });
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
  },

  props: ['username'],

  data: () => ({

    textAttrs: {
      contents: '',
      color: '#fff',
    },
    mediafile: null, // the photo

    stype: 'text',

    steps : {
      SELECT_STYPE: 'select-stype',
      EDIT: 'edit',
    },

    step: null,

  }),

  methods: {
    async shareStory() {
      console.log(`Sending stype ${this.stype}...`);
      const url = `/${this.username}/stories`;
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

    createTextStory(event) {
      this.stype = 'text';
      this.step = this.steps.EDIT;
    },

    createPhotoStory(event) {
      this.stype = 'image';
      this.step = this.steps.EDIT;
    },

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

</style>

