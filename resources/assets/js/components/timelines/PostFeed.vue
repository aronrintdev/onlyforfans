<template>
  <div class="feed-crate tag-posts tag-crate">

    <section class="row">
      <div v-for="(fi, idx) in feeditems" :key="fi.id" class="col-sm-12">
        <!-- for now we assume posts; eventually need to convert to a DTO -->
          <!-- img-src="https://picsum.photos/600/300/?image=25" -->
        <b-card title="Card Title" tag="article" class="OFF-mb-2">
          <b-card-img v-if="fi.mediafiles.length>0" src="https://picsum.photos/600/300/?image=25" alt="Image"></b-card-img>
          <b-card-text>
            {{ fi.description }}
          </b-card-text>
          <b-button href="#" variant="primary">Go somewhere</b-button>
        </b-card>

      </div>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
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
    ...Vuex.mapState(['feeditems']),

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
    this.$store.dispatch('getFeeditems', 'home');
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

