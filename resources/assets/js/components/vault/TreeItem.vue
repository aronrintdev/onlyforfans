<template>
  <li class="tag-tree-node"> 
    <span @click="doToggle(item)">
      <fa-icon :icon="isOpen ? ['fas', 'caret-down'] : ['fas', 'caret-right']" fixed-width class="OFF-mx-2" />
    </span>
    <span :class="{bold: true, active: currentFolderId===item.id}" @dblclick="makeFolder">
      <span @click="doNav(item)" class="node-name">{{ item.name }}</span>
    </span>
    <ul v-show="isOpen" class="pl-3">
      <TreeItem
        class="item"
        v-for="(child, index) in item.children"
        :key="index"
        :item="child"
        :currentFolderId="currentFolderId"
        @make-folder="$emit('make-folder', $event)"
        @add-item="$emit('add-item', $event)"
        @do-nav="$emit('do-nav', $event)"
      />
      <!--
      <li class="add" @click="$emit('add-item', item)">+</li>
      -->
    </ul>
  </li>
</template>
<script>
import Vue from 'vue'
import Vuex from 'vuex'

// see: https://vuejs.org/v2/examples/tree-view.html
export default {

  name: 'TreeItem',

  props: {
    item: Object,
    currentFolderId: null,
  },

  data: () => ({
    isOpen: true
  }),

  computed: {
    isLoading() {
      return false
    },
  },

  methods: {
    doNav(item) {
      this.isOpen = true
      this.$emit('do-nav', item.id)
    },
    doToggle(item) {
      this.isOpen = !this.isOpen;
    },
    makeFolder() {
      //if (!this.isFolder) {
        this.$emit("make-folder", this.item)
        this.isOpen = true
      //}
    },
  },

  created() { },

  watch: { },

  components: { 
  },
}

</script>

<style lang="scss" scoped>
body {
  ul {
    list-style: none;
  }
  .tag-tree-node { 
    .node-name:hover {
      cursor: pointer;
    }
    .active {
      color: red;
    }
  }
}
</style>
