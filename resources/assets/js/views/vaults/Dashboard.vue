<template>
  <div v-if="!isLoading" id="view-vault_dashboard" class="row flex-grow-1">
    <div class="col-sm-12">
      <VaultDashboardComponent
        :vault_pkid="myVault.id"
        :vaultfolder_pkid="vaultRootFolder.id"
        :session_user="session_user"
      />
    </div>
  </div>
</template>

<script>
import Vuex from 'vuex'
import VaultDashboardComponent from '@components/vault/Dashboard'

export default {

  components: {
    VaultDashboardComponent,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.myVault || !this.vaultRootFolder || !this.session_user
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
