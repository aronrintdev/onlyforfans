<template>
  <div v-if="!isLoading" id="view-vault_dashboard" class="row">
    <div class="col-sm-12">
      <VaultDashboardComponent
        :vault_pkid="myVault.id"
        :vaultfolder_pkid="vaultRootFolder.id"
      />
    </div>
  </div>
</template>

<script>
// Vaults Dashboard View
import VaultDashboardComponent from '@components/vault/Dashboard'

export default {

  components: {
    VaultDashboardComponent,
  },

  computed: {
    isLoading() {
      return !this.myVault || !this.vaultRootFolder
    },
  },

  data: () => ({
    myVault: null,
    vaultRootFolder: null,
  }),

  methods: {
    load() {
      this.axios.get(this.$apiRoute('vault.dashboard'))
        .then(response => {
          this.myVault = response.data.myVault
          this.vaultRootFolder = response.data.vaultRootFolder
        })
    },
  },

  created() {
    this.load()
  },

}
</script>
