<template>
  <div class="container-fluid supercrate-player">

    <section class="row">

      <aside class="col-md-3 tag-debug">
        <h2>My Story Views</h2>
        <hr />
      </aside>

      <main class="col-md-9 tag-debug">

        <nav :style="cssNav">
          <div v-for="(story, index) in stories">
            <div></div>
          </div>
        </nav>

        <section :style="cssDisplay" class="display-area bg-blur" v-for="(story, index) in stories" :key="index" v-if="current == index">
          <div class="bg-blur">
          </div>
          <div class="crate-content">
            <article v-if="stories[index].stype==='text'" class="h-100 v-wrap">
              <p class="h4 text-center v-box">{{ stories[index].content }}</p>
            </article>
            <article v-else-if="stories[index].stype==='image'" class="h-100">
              <img v-bind:src="stories[index].mf_url" class="OFF-img-fluid OFF-h-100" />
            </article>
          </div>
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
      current: 0,
    }
  },

  props: {
    username: String,
    stories: Array,
  },

  computed: {
    cssNav() {
      return {
        //'--bg-color': this.bgColor,
        //'--height': this.height + 'px',
        '--grid-template-columns': `repeat(${this.stories.length}, 1fr)`,
      }
    },
    cssDisplay() {
      if ( this.stories[this.current].mf_url ) {
        return {
          '--background-image': `url(${this.stories[this.current].mf_url})`,
        }
      }
      return {};
    },
  },

  mounted() {

    let timeline = anime.timeline({
      autoplay: true,
      duration: 2000,
      easing: 'linear',
      loop: true
    })

    this.stories.forEach((story, index) => {
      timeline.add({
        targets: document.querySelectorAll('.supercrate-player nav > div')[index].children[0],
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

.supercrate-player .display-area {
  position: relative;
  height: 70vh;
}

.supercrate-player .display-area > .crate-content {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  /*
  height: 60vh;
  */
}

.supercrate-player .display-area > .crate-content p {
  margin: auto;
}
.supercrate-player .display-area > .crate-content img {
  height: 100%;
  display: block;
  margin: auto;
}


.supercrate-player .display-area > .bg-blur {

  opacity: 0.4;
  background-image: var(--background-image);

  filter: blur(8px);
  -webkit-filter: blur(8px);

  width: 100%;
  height: 100%;

  /* Center and scale the image nicely */
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}

.supercrate-player nav {
  margin: 1rem 0;
  box-sizing: border-box;
  display: grid;
  grid-column-gap: 1em;
  /* grid-template-columns: repeat(4, 1fr); */
  grid-template-columns: var(--grid-template-columns);
  width: 100%;
  height: 0.7em;
  /*
  padding: 0 1em;
  position: fixed;
  top: 1em;
   */
}

.supercrate-player nav > div {
  background: rgba(0,0,0,0.25);
  height: 100%;
}

.supercrate-player nav > div > div {
  background: black;
  height: 100%;
  width: 0%;
}

/* Vertical centering, from https://stackoverflow.com/questions/396145/how-to-vertically-center-a-div-for-all-browsers */
body .v-wrap {
  height: 100%;
  text-align: center;
  white-space: nowrap;
}
body .v-wrap:before {
  content: "";
  display: inline-block;
  vertical-align: middle;
  width: 0;
  /*might want to tweak this. .25em for extra white space */
  height: 100%;
}
body .v-box {
  display: inline-block;
  vertical-align: middle;
  white-space: normal;
}
</style>
