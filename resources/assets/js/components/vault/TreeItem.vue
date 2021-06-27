<template>
  <li> 
    <div :class="{bold: isFolder}" @click="doToggle" @dblclick="makeFolder">
      {{ item.name }}
      <span v-if="isFolder">[{{ isOpen ? '-' : '+' }}]</span>
    </div>
    <ul v-show="isOpen" v-if="isFolder">
      <TreeItem
        class="item"
        v-for="(child, index) in item.children"
        :key="index"
        :item="child"
        @make-folder="$emit('make-folder', $event)"
        @add-item="$emit('add-item', $event)"
      />
      <li class="add" @click="$emit('add-item', item)">+</li>
    </ul>
  </li>
</template>
<script>
import Vue from 'vue'
import Vuex from 'vuex'
//import TreeItem from '@components/vault/TreeItem'

// see: https://vuejs.org/v2/examples/tree-view.html
export default {

  name: 'TreeItem',

  props: {
    item: Object
  },

  data: () => ({
    isOpen: true
  }),

  computed: {
    isFolder() {
      return this.item.children && this.item.children.length;
    },
    isLoading() {
      return false
    },
  },

  methods: {
    doToggle() {
      if (this.isFolder) {
        this.isOpen = !this.isOpen;
      }
    },
    makeFolder() {
      if (!this.isFolder) {
        this.$emit("make-folder", this.item);
        this.isOpen = true;
      }
    },
  },

  created() { },
  methods: { },
  watch: { },
  components: { 
    //TreeItem,
  },
}

</script>

<style lang="scss" scoped>
body {
}
</style>
