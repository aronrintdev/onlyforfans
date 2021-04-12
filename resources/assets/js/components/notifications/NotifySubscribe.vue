<template>
  <div v-if="!isLoading">
  Notify Subscribe
  </div>
</template>

<script>
//import Vuex from 'vuex';

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({

  }),

  watch: { },

  mounted() { },

  created() { },

  methods: {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

