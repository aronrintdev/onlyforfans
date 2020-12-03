<template>
  <div>
    <form v-on:submit.prevent>
      <textarea v-model="attrs.contents" id="story-contents" name="story-contents" rows="4"></textarea>
      <div>
        <div class="card card-default">
          <div class="card-header">Backgrounds</div>
          <div class="card-body">
            <div class="row list-of-bgcolors">
              <div><a role="button" tabindex="0" @click="setColor('cyan')" class="btn btn-sm col-md-1 tag-bg-cyan">&nbsp;</a></div>
              <div><a role="button" tabindex="0" @click="setColor('gold')" class="btn btn-sm col-md-1 tag-bg-gold">&nbsp;</a></div>
              <div><a role="button" tabindex="0" @click="setColor('gray')" class="btn btn-sm col-md-1 tag-bg-gray">&nbsp;</a></div>
              <div><a role="button" tabindex="0" @click="setColor('pink')" class="btn btn-sm col-md-1 tag-bg-pink">&nbsp;</a></div>
            </div>
          </div>
        </div>
      </div>
      <button @click="doCancel()" class="btn btn-default">Cancel</button>
      <button @click="shareStory()" type="submit" class="btn btn-primary">Share to Story</button>
    </form>
  </div>
</template>

<script>
import { eventBus } from '../../app';

export default {
  mounted() {
  },

  props: [
    'attrs',
  ],

  data: () => ({
  }),

  methods: {
    setColor(color) {
      this.$emit('set-color', color)
    },
    doCancel() {
      this.$emit('do-cancel')
    },
    shareStory() {
      eventBus.$emit('share-story')
    },
  },

  components: {
  },
}
</script>

<style scoped>
/* %FIXME %DRY */
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
