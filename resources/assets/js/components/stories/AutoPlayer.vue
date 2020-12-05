<template>
  <div class="container-fluid supercrate-player">

    <section class="row">

      <aside class="col-md-3 tag-debug">
        <h2>My Story Views</h2>
        <hr />
      </aside>

      <main class="col-md-9 tag-debug">

        <nav>
          <div v-for="(story, index) in stories">
            <div></div>
          </div>
        </nav>

        <section class="display-area" v-for="(story, index) in stories" :key="index" v-if="current == index">
          <article v-if="stories[index].stype==='text'">
            <p>{{ stories[index].content }}</p>
          </article>
          <article v-else-if="stories[index].stype==='image'">
            <img v-bind:src="stories[index].mf_url" class="img-fluid" />
          </article>
          <!-- <img class="img-responsive" src="{{ }}" /> -->
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
import anime from 'animejs';

export default {

  data() {
    return {
      //stories: ['#D53738', '#638867', '#FAF429'],
      current: 0
    }
  },

  props: {
    username: String,
    stories: Array,
  },

  mounted() {

    let timeline = anime.timeline({
      autoplay: true,
      duration: 10000,
      easing: 'linear',
      loop: true
    })

    this.stories.forEach((story, index) => {
      timeline.add({
        targets: document.querySelectorAll('nav > div')[index].children[0],
        width: '100%',
        changeBegin: (a) => {
          this.current = index
        }
      })
    })

  }

}

</script>

<style scoped>
.supercrate-player section.display-area {
  width: 100%;
  height: 500px;
  /*
  height: 100%;
   */
}

.supercrate-player nav{
  box-sizing: border-box;
  display: grid;
  grid-column-gap: 1em;
  grid-template-columns: repeat(3, 1fr);
  height: 0.5em;
  padding: 0 1em;
  /*
  position: fixed;
   */
top: 1em;
width: 100%;
}

.supercrate-player nav > div{
  background: rgba(0,0,0,0.25);
  height: 100%;
}

.supercrate-player nav > div > div{
  background: black;
  height: 100%;
  width: 0%;
}
</style>
