<template>
  <div id="view-vault_dashboard" class="row">
    <div class="col-sm-12">
      <Dashboard
        :vault_pkid="myVault.id"
        :vaultfolder_pkid="vaultRootFolder.id"
      />
    </div>
  </div>
</template>

<script>
/**
 * Vaults Dashboard View
 */
import Dashboard from '@components/vault/Dashboard'
export default {
  components: {
    Dashboard,
  },
  data: () => ({
    loading: true,
    myVault: null,
    vaultRootFolder: null,
  }),
  methods: {
    load() {
      this.loading = true
      this.axios.get(this.$apiRoute('vault.dashboard'))
        .then(response => {
          this.myVault = response.data.myVault
          this.vaultRootFolder = response.data.vaultRootFolder
          this.loading = false
        })
    },
  },
  created() {
    this.load()
  }
}
</script>
