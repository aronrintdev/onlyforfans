<template>
  <div>
    <div v-if="!isNewManager">
      <b-card class="mb-3">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div v-if="!mobile" class="h3 text-center">{{ $t('title') }}</div>
            <b-button variant="primary" class="px-4 ml-auto" @click="isNewManager=true">Invite Manager</b-button>
          </div>
          <staff-member-table :items="managers" :metadata="metadata" @load="loadPage" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite v-if="isNewManager" :session_user="session_user" @exit="isNewManager=false" @send="inviteSent"></staff-invite>
  </div>
</template>

<script>
import Vuex from 'vuex'
import StaffMemberTable from './StaffMemberTable';
import StaffInvite from './StaffInvite';

export default {
  props: {
    session_user: null,
  },

  components: {
    StaffMemberTable,
    StaffInvite,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
  },

  data: () => ({
    managers: [],
    metadata: {},
    isNewManager: false,
  }),

  mounted() {
    // Get managers list of current login user
    this.loadPage(1);
  },

  methods: {

    inviteSent() {
      this.isNewManager = false;
      this.loadPage(1);
    },

    async loadPage(page) {
      const response = await axios.get( this.$apiRoute('staffaccounts.index'), {
        params: {
          page: page ? page : this.metadata.current_page,
          role: 'manager'
        }
      })
      this.managers = response.data.data;
      this.metadata = response.data.meta;
    }
  },

}
</script>

<style lang="scss" scoped>

</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Managers",
  }
}
</i18n>

