<template>
  <b-breadcrumb class="pl-0 my-0">
    <b-breadcrumb-item
      v-for="bc in breadcrumbNav"
      :key="bc.pkid"
      :active="bc.active"
      @click="doNav(bc.pkid)"
    >
      {{ bc.text }}
    </b-breadcrumb-item>
  </b-breadcrumb>
</template>

<script>
import Vuex from 'vuex'

export default {
  name: 'Breadcrumb',

  components: {},

  props: {},

  computed: {
    ...Vuex.mapState(['breadcrumb']),
    ...Vuex.mapState('vault', [ 'vaultRootFolder', 'currentFolderId' ]),

    breadcrumbNav() {
      return this.breadcrumb ? this.breadcrumb.map(b => ({
        pkid: b.pkid,
        text: b.vfname === 'Root' ? 'Home' : b.vfname,
        active: b.pkid === this.currentFolderId
      })) : []
    },
  },

  data: () => ({}),

  methods: {
    ...Vuex.mapMutations('vault', [ 'UPDATE_CURRENT_FOLDER_ID' ]),

    async doNav(vaultfolderId) {
      this.UPDATE_CURRENT_FOLDER_ID(vaultfolderId)
      this.$store.dispatch('getVaultfolder', vaultfolderId)
    },
  },

  watch: {},

  created() {
    this.UPDATE_CURRENT_FOLDER_ID(this.vaultRootFolder ? this.vaultRootFolder.id : null)
  },
}
</script>

<style lang="scss" scoped>
.breadcrumb {
  background-color: #fff;
  border-radius: 0;
}
.breadcrumb .breadcrumb-item {
  font-size: 1.2rem;
}
.breadcrumb .breadcrumb-item.active {
  color: #212529;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
