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
            v-on:share-to-story="shareToStory($event)"
            v-on:doCancel="step=steps.SELECT_STYPE"
          ></text-story-form>
        </div>

      </aside>

      <main class="col-md-9 tag-debug">
        <div v-if="step===steps.SELECT_STYPE" class="step-select_stype">
          <section class="row">
            <article class="col-md-6">
              <div @click="createPhotoStory()" class="clickme_to-create tag-bg-cyan">Create a Photo Story</div>
            </article>
            <article class="col-md-6">
              <div @click="createTextStory()" class="clickme_to-create tag-bg-pink">Create a Text Story</div>
            </article>
          </section>
        </div>

        <div v-if="step===steps.EDIT" class="step-edit">
          <text-story-preview 
              v-if="stype==='text'" 
              v-bind:attrs="textAttrs" 
              username="username"
          ></text-story-preview>
          <photo-story-form v-if="stype==='photo'"></photo-story-form>
        </div>

      </main>

    </section>
  </div>
</template>

<script>
import TextStoryForm from './TextStoryForm.vue';
import PhotoStoryForm from './PhotoStoryForm.vue';
import TextStoryPreview from './TextStoryPreview.vue';
import PhotoStoryPreview from './PhotoStoryPreview.vue';

export default {

  mounted() {
    this.step = this.steps.SELECT_STYPE;
  },

  props: ['username'],

  data: () => ({

    textAttrs: {
      contents: '',
      color: '#fff',
    },

    stype: 'text',

    steps : {
      SELECT_STYPE: 'select-stype',
      EDIT: 'edit',
    },

    step: null,

  }),

  methods: {
    async shareToStory($event, attrs) {
      console.log(`Sending stype ${this.stype}...`);
      const url = `/${this.username}/stories`;
      let payload;

      switch ( this.stype ) {
        case 'text':
          payload = {
            stype: 'text',
            bgcolor: this.textAttrs.color,
            content: this.textAttrs.contents,
          };
          break;
        case 'image':
          payload = new FormData();
          payload.append('photo', this.photo);
          const json = JSON.stringify({
              stype: 'image',
              bgcolor: this.textAttrs.color,
              content: this.textAttrs.contents,
          });
          payload.append('attrs', json);
          break;
      } 

      const response = await axios.post(url, payload);
      console.log('shareToStory()', {response});
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
      this.stype = 'photo';
      this.step = this.steps.EDIT;
    },

    selectFile(event) {
      // `files` is always an array because the file input may be in multiple mode
      this.photo = event.target.files[0];
    }
    /*
    async shareToStory(color) {
      console.log(`Setting color: ${color}`);
      const url = `/${this.username}/stories`;
      const payload = {
        stype: 'text',
        bgcolor: this.color,
        content: this.contents,
      };
      const response = await axios.post(url, payload);
      console.log('shareToStory()', {response});
    },
    setColor(color) {
      console.log(`Setting color: ${color}`);
      this.color = color;
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
}

.tag-bg-cyan {
  background-color: cyan;
}
.tag-bg-gold {
  background-color: gold;
}
.tag-bg-gray {
  background-color: gray;
}
.tag-bg-pink {
  background-color: pink;
}

</style>

