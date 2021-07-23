<template>
  <div>
    <div v-if="!isNewManager">
      <b-card class="mb-3">
        <b-card-text>
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="h3 text-center">Managers</div>
            <b-button variant="primary" class="px-4" @click="isNewManager=true">Invite Manager</b-button>
          </div>
          <staff-member-table :items="managers" />
        </b-card-text>
      </b-card>
    </div>
    <staff-invite v-if="isNewManager" :session_user="session_user" @exit="isNewManager=false" @send="inviteSent"></staff-invite>
  </div>
</template>

<script>
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

export default {
  props: {
    session_user: null,
  },

  components: {
    StaffMemberTable,
    StaffInvite,
  },

  data: () => ({
    managers: MockData,
    isNewManager: false,
  }),

  methods: {
    inviteSent(data) {
      if (this.isNewManager) {
        this.managers.push(data);
        this.isNewManager = false;
      }
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
