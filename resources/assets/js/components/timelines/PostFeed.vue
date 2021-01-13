<template>
  <div class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div class="col">

        <b-card
          title="Card Title"
          img-src="https://picsum.photos/600/300/?image=25"
          img-alt="Image"
          img-top
          tag="article"
          class="OFF-mb-2"
          >
          <b-card-text>
            Some quick example text to build on the card title and make up the bulk of the card's content.
          </b-card-text>

          <b-button href="#" variant="primary">Go somewhere</b-button>
        </b-card>

      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import vue2Dropzone from 'vue2-dropzone';
//import 'vue2-dropzone/dist/vue2Dropzone.min.css';
//import { eventBus } from '../../app';

export default {

  props: {
    /*
    vault_pkid: {
      required: true,
      type: Number,
    },
     */
  },

  computed: {
    //...Vuex.mapState(['shares']),

    /*
    mediafiles() {
      return this.vaultfolder.mediafiles;
    },
     */
  },

  data: () => ({

    /*
    showCreateForm: false,
    shareForm: {
      selectedToShare: [],
    },
     */

  }),

  mounted() {
  },

  created() {
    //this.currentFolderPKID = this.vaultfolder_pkid;
    //this.$store.dispatch('getVault', this.vault_pkid);
  },

  methods: {

  },

  components: {
    //vueDropzone: vue2Dropzone,
    //VueAutosuggest,
  },
}
</script>

<style scoped>
</style>

