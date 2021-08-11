<template>
  <div class="container p-0">
    <b-card header-tag="header" footer-tag="footer" class="position-relative">

      <template #header>
        <section class="d-flex tag-modal-header">
          <div class="my-auto mr-3">
            <div class="h5 mb-0" v-text="$t('title')" />
          </div>
          <div class="tag-ctrl d-flex align-items-center flex-grow-1">
            <button type="button" @click="discard" aria-label="Close" class="close ml-auto">×</button>
          </div>
        </section>
      </template>

      <LoadingOverlay :loading="loading" />
      <VaultSelectorComponent @close="exit" ref="vaultSelector" />

      <template #footer>
        <b-row>
          <b-col cols="12" class="d-flex justify-content-end">
            <b-btn class="px-3" variant="primary" :disabled="!isSaveable" @click="applySelection">Select</b-btn>
          </b-col>
        </b-row>
      </template>

    </b-card>
  </div>
</template>
<script>
import { eventBus } from '@/eventBus'
import LoadingOverlay from '@components/common/LoadingOverlay'
import VaultSelectorComponent from '@views/live-chat/components/ShowThread/VaultSelector'

export default {
  props: {
    payload: null,
  },

  computed: {
    isSaveable() {
      return true // (this.loading) ? false  : ...
    }
  },

  data: () => ({
    loading: false,
  }),

  methods: {

    // %NOTE: this is coupled to the VaultSelector child component, essentially assumes its onSelect() method is 'public'
    applySelection() {
      this.$refs.vaultSelector.applySelection()
    },

    discard(e) {
      this.exit()
    },

    exit() {
      this.$bvModal.hide('modal-vault-selector');
    },

  },

  mounted() { },

  components: {
    LoadingOverlay,
    VaultSelectorComponent,
  },

  name: "VaultSelectorModal",

}
</script>

<style lang="scss" scoped>
textarea,
.dropzone,
.vue-dropzone {
  border: none;
}
.select-calendar {
  cursor: pointer;
  align-self: center;
}
.tag-modal-header button.close {
  padding: 1rem;
  margin: -1rem -1rem -1rem auto;
  line-height: 1em;
}
</style>
<i18n lang="json5" scoped>
{
  "en": {
    "title": "Select Files",
    "loading": {
      "error": "An error has occurred. Please return to the previous page and try again later."
    },
    "save": {
      "button": "Save",
      "error": "An error has occurred. Please try again later."
    },
    "priceForFollowers": "Price for free followers",
    "priceForSubscribers": "Price for paid subscribers",
  }
}
</i18n>
