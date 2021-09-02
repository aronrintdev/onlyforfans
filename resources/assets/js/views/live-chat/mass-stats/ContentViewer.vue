<template>
  <div class="tag-preview">
    <div v-if="!isTooLong">
      <p class="m-0">{{ str }}...</p> 
    </div>
    <div v-else-if="isExpanded">
      <p class="m-0">{{ str }}...</p> 
      <span @click="isExpanded=false" class="clickable"><em>Collapse</em></span>
    </div>
    <div v-else>
      <p class="m-0">{{ truncated(str) }}...</p> 
      <span @click="isExpanded=true" class="clickable"><em>Read more...</em></span>
    </div>
  </div>
</template>

<script>
import Vuex from 'vuex'
import Vue from 'vue'

export default {
  computed: {
  }, // computed()

  props: {
    str: '',
  },

  data: () => ({
    maxLength: 150, // maximum number of characters to extract
    isExpanded: false,
    isTooLong: true,
  }), // data

  methods: {

    truncated(str) {
      // ref: https://stackoverflow.com/questions/5454235/shorten-string-without-cutting-words-in-javascript
      //return str.replace(/^(.{3}[^\s]*).*/, "$1")

      let trimmedString = str.substr(0, this.maxLength) //trim the string to the maximum length
      return trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) //re-trim if we are in the middle of a word
    },

  },

  created() {
    if (this.str.length < this.maxLength) { 
      this.isTooLong = false
      this.isExpanded = true
    }
  },

  name: 'ContentViewer',

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}

td { 
}
</style>
