<template>
  <b-row :no-gutters="true">
    <b-col cols="12" md="3" v-for="(mf) in mediafiles" :key="mf.id" role="button">
      <PreviewFile
        :data-mf_id="mf.id"
        :mediafile="mf"
        @input="onPreviewFileInput"
        @render-lightbox="renderLightbox"
        class="p-1"
      />
    </b-col>
    <b-col v-for="(vf) in children" :key="vf.id" cols="12" md="3" role="button">
      <b-img-lazy fluid @click="doNav(vf.id)" src="/images/tmp-placeholders/folder-icon.jpg" :alt="`Folder ${vf.slug}`"></b-img-lazy>
      <div class="text-center">{{ vf.name }}</div>
    </b-col>
    <b-modal v-model="isMediaLightboxModalVisible" id="modal-media-lightbox" title="" hide-footer body-class="p-0" size="xl">
      <MediaLightbox :session_user="session_user" :mediafile="lightboxSelection" />
    </b-modal>
  </b-row>
</template>

<script>
/**
 * resources/assets/js/components/vault/MediaList.vue
 */
import Vue from 'vue'
import Vuex from 'vuex'
import PreviewFile from './PreviewFile'
import MediaLightbox from './MediaLightbox'

export default {
  name: 'MediaList',

  components: {
    MediaLightbox,
    PreviewFile,
  },

  props: {
    mediafiles: { type: [Array, Object], default: () => ([]) },
    children: { type: [Array, Object], default: () => ([]) },
  },

  computed: {
    ...Vuex.mapState(['session_user']),
  },

  data: () => ({
    lightboxSelection: null,
    isMediaLightboxModalVisible: false,
  }),

  methods: {
    ...Vuex.mapMutations('vault', [ 'UPDATE_CURRENT_FOLDER_ID' ]),

    async doNav(vaultfolderId) {
      this.UPDATE_CURRENT_FOLDER_ID(vaultfolderId)
      this.$store.dispatch('getVaultfolder', vaultfolderId)
    },

    onPreviewFileInput(value) {
      Vue.set(this.mediafiles, value.id, value) // Sets .selected on mediafiles array depending on child form component's action
    },

    renderLightbox(mediafile) {
      this.lightboxSelection = mediafile
      this.isMediaLightboxModalVisible = true
    },
  },

  watchers: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
