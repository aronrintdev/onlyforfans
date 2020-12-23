<template>
  <div class="container-fluid vault-container">

    <section class="row h-100">

      <aside class="col-md-3">

        <h2 class="my-3">My Saved</h2>

      </aside>

      <main class="col-md-9 OFF-d-flex OFF-align-items-center">

        <section class="row">
          <div class="col-sm-12">
            HERE 1
            {{ this.shareables }}
          </div>
        </section>

        <section class="row mt-5">
          <div class="col-sm-12">
            HERE
          </div>
        </section>

      </main>

    </section>
  </div>
</template>

<script>
import Vuex from 'vuex';
//import { eventBus } from '../../app';

export default {

  props: {
    vault_pkid: {
      required: true,
      type: Number,
    },
    vaultfolder_pkid: { // init value
      required: true,
      type: Number,
    },
  },

  computed: {
    ...Vuex.mapState(['shareables']),
  },

  data: () => ({
    show: true,
  }),

  created() {
    this.$store.dispatch('getShareables');
  },

  methods: {
  },

  components: {
    //vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
</style>
