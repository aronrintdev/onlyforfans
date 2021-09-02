<template>
  <div @click="isExpanded=!isExpanded" class="tag-preview clickable">
    <div v-if="!isTooLong">
      <p class="m-0" :class="{ 'text-right': mobile }">{{ str }}</p> 
    </div>
    <div v-else-if="isExpanded">
      <p class="m-0 OFF-truncate-single-line">{{ str }}</p> 
      <!--
      <div v-if="!mobile" @click="isExpanded=false" class="clickable"><span class="text-primary">Collapse</span></div>
      -->
    </div>
    <div v-else>
      <p class="m-0 truncate-single-line"  :class="{ 'text-right': mobile }">{{ truncated(str) }}...</p> 
      <!--
      <div v-if="!mobile" @click="isExpanded=true" class="clickable"><span class="text-primary">Read more</span></div>
      -->
    </div>
  </div>
</template>

<script>
import Vuex from 'vuex'
import Vue from 'vue'

export default {
  computed: {
    ...Vuex.mapState(['mobile']),
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

.tag-preview .truncate-single-line { 
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
