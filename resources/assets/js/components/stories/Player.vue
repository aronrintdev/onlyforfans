<template>
  <div class="container-fluid">

    <section class="row">

      <aside class="col-md-3 tag-debug">
        <h2>My Story Views</h2>
        <hr />
      </aside>

      <main class="col-md-9 tag-debug">
        <section>
          <article v-if="stories[index].stype==='text'">
            <p>{{ stories[index].content }}</p>
          </article>
          <article v-else-if="stories[index].stype==='image'">
            <img v-bind:src="stories[index].mf_url" class="img-fluid" />
          </article>
          <!--
          <img class="img-responsive" src="{{ }}" />
          -->
        </section>
        <section class="my-3 d-flex justify-content-between">
          <button @click="doNav('previous')" class="">Previous</button>
          <button @click="doNav('next')" class="">Next</button>
        </section>
      </main>

    </section>
  </div>
</template>

<script>
import { eventBus } from '../../app';

export default {

  mounted() {
    //this.step = this.steps.SELECT_STYPE;
  },

  created() {
    console.log('player created', {stories: this.stories});
    /*
    eventBus.$on('share-story', () => {
      this.shareStory();
    });
     */
  },

  props: {
    username: String,
    stories: Array,
  },

  data() {
    return {
      index: 0,
    }
  },

  methods: {
    doNav(dir) {
      switch (dir) {
        case 'previous':
          this.index = Math.max(this.index-1, 0);
          break;
        case 'next':
          this.index = Math.min(this.index+1, this.stories.length-1);
          break;
      }
      console.log(`index: ${this.index}`);
    }
  },

  components: {
    //textStoryForm: TextStoryForm,
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
</style>
