<template>
  <div class="component-vault-selector">
    <div class="d-flex">
    </div>
    <!-- <Dashboard
      v-if="!isLoading"
      :session_user="session_user"
      :vault_pkid="myVault.id"
      :vaultfolder_pkid="vaultRootFolder.id"
    /> -->
    <Breadcrumb v-if="!isLoading" class="mb-2" />
    <MediaList
      class="mb-3"
      :mediafiles="mediafiles"
      :children="children"
    />
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread/VaultSelector.vue
 *
 * Wrapper Class for vault selection
 */
import Vue from 'vue'
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import Dashboard from '@components/vault/Dashboard'
import Breadcrumb from '@components/vault/Breadcrumb'
import MediaList from '@components/vault/MediaList'

export default {
  name: 'VaultSelector',

  components: {
    Breadcrumb,
    Dashboard,
    MediaList,
  },

  props: {
    payload: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'vaultfolder', 'breadcrumb']),
    ...Vuex.mapState('vault', [
      'myVault',
      'vaultRootFolder'
    ]),

    children() {
      return this.vaultfolder ? this.vaultfolder.vfchildren : []
    },

    isLoading() {
      return !this.myVault || !this.vaultRootFolder || !this.session_user
    },

    context() {
      return this.payload?.context || ''
    },
  },

  data: () => ({
    mediafiles: {},
  }),

  methods: {
    ...Vuex.mapActions('vault', [
      'loadVaultDashboard',
    ]),
    ...Vuex.mapMutations('vault', [
      'ADD_SELECTED_MEDIAFILES',
    ]),

    load() {
      this.loadVaultDashboard()
        .then(() => {
          this.axios.get(route('vaults.show', { id: this.myVault.id })).then((response) => {
            this.vault = response.data.vault
            this.foldertree = response.data.foldertree || null
            this.$store.dispatch('getVaultfolder', this.vaultRootFolder.id)
          })
        })
        .catch(error => {
          eventBus.$emit('error', {error, message: this.$t('error.loading')})
        })

      if (this.vaultfolder && this.vaultfolder.mediafiles) {
        const selected = false
        this.mediafiles = _.keyBy(this.vaultfolder.mediafiles.map(o => ({ ...o, selected })), 'id')
      }
    },

    onClose() {
      this.$emit('close')
    },

    applySelection() {
      // Add selected Mediafiles to the selected media files list, then close
      const selected = _.filter(this.mediafiles, o => o.selected)
      this.ADD_SELECTED_MEDIAFILES(selected)
      eventBus.$emit('vaultselector-mediafiles-selected', { foo: 'bar' } )
      this.$emit('close')
    }
  },

  watch: {
    vaultfolder (newVal, oldVal) {
      if (newVal.mediafiles) {
        const selected = false
        this.mediafiles = _.keyBy(newVal.mediafiles.map(o => ({ ...o, selected })), 'id')
      }
    },
  },

  created() {
    this.load()
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "error": {
      "loading": "An error has occurred while loading your vault, please try again later."
    },
    "header": "Select from My Media",
    "selectButton": "Select"
  }
}
</i18n>
