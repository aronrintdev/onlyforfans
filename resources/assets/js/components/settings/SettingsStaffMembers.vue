<template>
  <div>
    <div v-if="!isNewStaff">
      <b-card class="mb-3">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">Staff Members</div>
            <b-button variant="primary" class="px-4" @click="isNewStaff=true">Invite Staff Member</b-button>
          </div>
          <staff-member-table :items="staffs" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite v-if="isNewStaff" :forManager="false" :session_user="session_user" @exit="isNewStaff=false" @send="inviteSent"></staff-invite>
  </div>
</template>

<script>
import StaffMemberTable from './StaffMemberTable';
import StaffInvite from './StaffInvite';

const StaffMockData = [
  {
    id: 1, 
    name: 'James Wolf',
    email: 'jaems.wolf@email.com',
    active: 1,
    last_login_at: '2021-07-05T18:03:02.000000Z',
    actions: null,
  },
  {
    id: 3, 
    name: 'Winter Prince',
    email: 'winter.prince@email.com',
    active: 0,
    last_login_at: '2021-07-21T18:03:02.000000Z',
    actions: null,
  },
  {
    id: 2, 
    name: 'Peter Tim',
    email: 'peter.tim@email.com',
    pending: 1,
    last_login_at: '2021-06-15T18:03:02.000000Z',
    actions: null,
  },
]


export default {
  props: {
    session_user: null,
  },

  components: {
    StaffMemberTable,
    StaffInvite,
  },

  data: () => ({
    staffs: StaffMockData,
    isNewStaff: false,
  }),

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
