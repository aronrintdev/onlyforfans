<template>
  <div v-if="!isLoading">
  Notify Like
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

  created() {
    //this.currentFolderPKID = this.vaultfolder_pkid;
    //this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid);
    //this.$store.dispatch('getVault', this.vault_pkid);
    axios.get( route('likes.index') ).then( response => {
    })
  },

  methods: {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

