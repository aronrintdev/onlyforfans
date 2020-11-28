<template>
  <div class="container">

    <div class="row OFF-justify-content-center">
      <aside class="col-md-3">
        <form v-on:submit.prevent>
          <textarea v-model="contents" id="story-contents" name="story-contents" rows="4"></textarea>
          <div>
            <div class="card card-default">
              <div class="card-header">Backgrounds</div>
              <div class="card-body">
                <div class="row list-of-bgcolors">
                  <div><a role="button" tabindex="0" @click="setColor('cyan')" class="btn btn-sm col-md-1 tag-cyan">&nbsp;</a></div>
                  <div><a role="button" tabindex="0" @click="setColor('gold')" class="btn btn-sm col-md-1 tag-gold">&nbsp;</a></div>
                  <div><a role="button" tabindex="0" @click="setColor('gray')" class="btn btn-sm col-md-1 tag-gray">&nbsp;</a></div>
                  <div><a role="button" tabindex="0" @click="setColor('pink')" class="btn btn-sm col-md-1 tag-pink">&nbsp;</a></div>
                </div>
              </div>
            </div>
          </div>
          <button class="btn btn-default">Cancel</button>
          <button @click="shareToStory()" type="submit" class="btn btn-primary">Share to Story</button>
        </form>
      </aside>

      <main class="col-md-9">
        <div class="card card-default">
          <div class="card-header">Preview ({{ username }})</div>
          <div v-bind:style="{ backgroundColor: color }" class="card-body">
            {{ contents }}
          </div>
        </div>
      </main>
    </div>

  </div>
</template>

<script>
export default {
  mounted() {
    console.log('Component mounted.')
  },
  props: ['username'],
  data() {
    return {
      contents: '',
      color: '#fff',
    }
  },
  methods: {
    async shareToStory(color) {
      console.log(`Setting color: ${color}`);
      const url = `/${this.username}/stories/store`;
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
  }
}
</script>

<style scoped>
.tag-cyan {
  background-color: cyan;
}
.tag-gold {
  background-color: gold;
}
.tag-gray {
  background-color: gray;
}
.tag-pink {
  background-color: pink;
}

</style>
