<template>
  <div>
    <div v-if="!isNewStaff && !isNewManager">
      <b-card class="mb-3">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">Managers</div>
            <b-button variant="primary" class="px-4" @click="isNewManager=true">Invite Manager</b-button>
          </div>
          <staff-member-table :items="managers" />
        </b-card-text>
      </b-card>
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
    <staff-invite v-if="isNewManager" @exit="isNewManager=false" @send="inviteSent"></staff-invite>
    <staff-invite v-if="isNewStaff" :forManager="false" @exit="isNewStaff=false" @send="inviteSent"></staff-invite>
  </div>
</template>

<script>
import moment from 'moment'
import StaffMemberTable from './StaffMemberTable';
import StaffInvite from './StaffInvite';

const MockData = [
  {
    id: 1, 
    name: 'John Doe',
    email: 'john.doe@email.com',
    active: 1,
    role: 'Admin',
    last_login_at: '2021-07-15T18:03:02.000000Z',
    actions: null,
  },
  {
    id: 2, 
    name: 'Smith Jane',
    email: 'smith.jane@email.com',
    active: 1,
    role: 'Staff',
    last_login_at: '2021-06-15T18:03:02.000000Z',
    actions: null,
  },
  {
    id: 3, 
    name: 'Walter Pick',
    email: 'walter.pick@email.com',
    active: 0,
    role: 'Staff',
    last_login_at: '2021-07-21T18:03:02.000000Z',
    actions: null,
  },
  {
    id: 4, 
    name: 'Michael Johnson',
    email: 'michael.johnson@email.com',
    active: 0,
    pending: 1,
    role: 'Staff',
    last_login_at: '2021-07-11T18:03:02.000000Z',
    actions: null,
  }
]

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
  components: {
    StaffMemberTable,
    StaffInvite,
  },

  data: () => ({
    managers: MockData,
    staffs: StaffMockData,
    isNewManager: false,
    isNewStaff: false,
  }),

  methods: {
    inviteSent(data) {
      if (this.isNewManager) {
        this.managers.push(data);
        this.isNewManager = false;
      } else {
        this.staffs.push(data);
        this.isNewStaff = false;
      }
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
