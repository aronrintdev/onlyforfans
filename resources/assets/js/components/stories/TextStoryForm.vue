<template>
  <div>
    <form v-on:submit.prevent>
      <textarea v-model="attrs.contents" id="story-contents" name="story-contents" rows="8" class="w-100"></textarea>
      <div class="card card-default mt-3">
        <div class="card-header">Backgrounds</div>
        <div class="card-body">
          <div class="d-flex list-of-bgcolors">
            <div><a role="button" tabindex="0" @click="setColor('cyan')" class="btn btn-sm tag-bg-cyan">&nbsp;</a></div>
            <div><a role="button" tabindex="0" @click="setColor('gold')" class="btn btn-sm tag-bg-gold">&nbsp;</a></div>
            <div><a role="button" tabindex="0" @click="setColor('gray')" class="btn btn-sm tag-bg-gray">&nbsp;</a></div>
            <div><a role="button" tabindex="0" @click="setColor('pink')" class="btn btn-sm tag-bg-pink">&nbsp;</a></div>
          </div>
        </div>
      </div>
      <div class="card card-default mt-3">
        <div class="card-header">Swipe Up Link</div>
        <b-form-input class="swipe-up-link" type="url" v-model="attrs.link" :state="urlState"></b-form-input>
      </div>
      <div class="superbox-ctrl d-sm-flex justify-content-around mt-3 mb-3 mb-sm-0">
        <button @click="shareStory()" type="submit" class="btn btn-primary mb-1 mb-sm-0">Share to Story</button>
        <button @click="doCancel()" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script>
import { eventBus } from '@/app';
import validateUrl from '@helpers/validateUrl';

export default {
  mounted() {
  },

  props: [
    'attrs',
  ],

  computed: {
    urlState() {
      if (!this.attrs.link) {
        return null
      }
      return validateUrl(this.attrs.link)
    }
  },

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

form .superbox-ctrl .btn {
  width: 100%;
}
/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) { 
  form .superbox-ctrl .btn {
    width: 45%;
  }
}
body textarea#story-contents {
  resize: none;
  border: solid #e5e5e5 2px;
  border-radius: 5px;
}
body .list-of-bgcolors .btn {
  width: 1.7rem;
  height: 1.7rem;
  padding: 0; /* 0.7rem; */
  border-radius: 50%;
  margin-right: 0.3rem;
}
.swipe-up-link {
  border-width: 0px;
}
</style>
