<template>
  <div>
    <div v-if="!isNewStaff">
      <b-card class="mb-3" v-for="(team, index) in teams" :key="index">
        <b-card-text v-if="team">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">{{ team.owner.name }} team</div>
            <b-button variant="primary" class="px-4" @click="isNewStaff=true;creator_id=team.owner.id">Invite Staff Member</b-button>
          </div>
          <staff-member-table :items="team.members" :has_pagination="false" @load="loadPage" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite
      v-if="isNewStaff"
      :creator_id="creator_id"
      :session_user="session_user"
      @exit="isNewStaff=false"
      @send="inviteSent">
    </staff-invite>
  </div>
</template>

<script>
import StaffMemberTable from './StaffMemberTable';
import StaffInvite from './StaffInvite';

export default {
  props: {
    session_user: null,
    user_settings: null,
  },

  components: {
    StaffMemberTable,
    StaffInvite,
  },

  data: () => ({
    teams: [],
    isNewStaff: false,
    creator_id: null,
  }),

  created() {
    this.loadPage();
  },

  methods: {
    inviteSent() {
      this.isNewStaff = false;
      this.loadPage();
    },
    loadPage() {
      this.axios.get(this.$apiRoute('staff.indexStaffMembers'))
        .then(response => {
          this.teams = response.data;
        })
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
