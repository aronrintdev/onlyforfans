<template>
  <div>
    <div v-if="!isNewManager">
      <b-card class="mb-3">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">Managers</div>
            <b-button variant="primary" class="px-4" @click="isNewManager=true">Invite Manager</b-button>
          </div>
          <staff-member-table :items="managers" :metadata="metadata" @load="loadPage" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite v-if="isNewManager" :session_user="session_user" @exit="isNewManager=false" @send="inviteSent"></staff-invite>
  </div>
</template>

<script>
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
    loadPage(page) {
      axios.get(this.$apiRoute('staff.indexManagers'), {
        params: {
          page: page ? page : this.metadata.current_page,
        }
      })
        .then(response => {
          this.managers = response.data.data;
          this.metadata = response.data.meta;
        })
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
