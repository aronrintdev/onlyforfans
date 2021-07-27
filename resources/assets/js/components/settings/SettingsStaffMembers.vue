<template>
  <div>
    <div v-if="!isNewStaff">
      <b-card class="mb-3" v-for="(team, index) in teams" :key="index">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">{{ team.owner.name }} team</div>
            <b-button variant="primary" class="px-4" @click="isNewStaff=true">Invite Staff Member</b-button>
          </div>
          <staff-member-table :items="team.members" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite v-if="isNewStaff" :forManager="false" :session_user="session_user" @exit="isNewStaff=false" @send="inviteSent"></staff-invite>
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
  }),

  created() {
    this.axios.get(this.$apiRoute('staff.indexStaffMembers'))
      .then(response => {
        this.teams = response.data;
      })
  },

  methods: {
    inviteSent(data) {
      if (this.isNewStaff) {
        this.staffs.push(data);
        this.isNewStaff = false;
      }
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
