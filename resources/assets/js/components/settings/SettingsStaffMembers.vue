<template>
  <div>
    <b-card class="mb-3" v-if="isMain">
      <b-card-text>
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="h3 text-center">Managers</div>
          <b-button variant="primary" class="px-4" @click="isMain=false">Invite Manager</b-button>
        </div>
        <b-table hover
          id="staff-members-table"
          :items="members"
          :fields="fields"
          :current-page="currentPage"
        >
          <template #cell(role)="data">
            <div class="role-select px-0" v-if="!isEditingRole" @click="changeRole">{{ data.item.role }}</div>
            <b-form-select class="role-select" v-if="isEditingRole" @change="isEditingRole=false" v-model="data.item.role" :options="options.roles"></b-form-select>
          </template>
          <template #cell(active)="data">
            <b-badge variant="info" v-if="data.item.pending">Invited</b-badge>
            <b-badge variant="success" v-else-if="data.item.active">Active</b-badge>
            <b-badge variant="secondary" v-else class="text-white">Disabled</b-badge>
          </template>
          <template #cell(actions)="data">
            <div class="text-right">
              <b-button class="actions-btn" variant="secondary" size="sm" @click="changeActive(data.index)" v-if="!data.item.pending && data.item.active">Deactivate</b-button>
              <b-button class="actions-btn" variant="success" size="sm" @click="changeActive(data.index)" v-if="!data.item.pending && !data.item.active">Activate</b-button>
              <b-button class="actions-btn remove-btn" variant="danger" size="sm" @click="removeMember(data.index)">
                <fa-icon :icon="['fas', 'trash']" size="sm" class="text-white" />
              </b-button>
            </div>
          </template>
        </b-table>
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          aria-controls="loginSessions-table"
          v-on:page-click="pageClickHandler"
        ></b-pagination>
      </b-card-text>
    </b-card>
    <b-card class="mb-3" v-if="!isMain">
      <b-card-text>
        <a class="text-primary text-decoration-none link-btn" @click="isMain=true">Back</a>
        <div class="d-flex align-items-center my-4">
          <div class="h3 text-center">Add Manager</div>
        </div>
        <b-form @submit.prevent="submitAddMemberForm($event)">
          <b-row>
            <b-col>
              <b-form-group label="First name" label-for="first_name">
                <b-form-input id="first_name" v-model="formProfile.first_name" placeholder="First name" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group label="Last name" label-for="last_name">
                <b-form-input id="last_name" v-model="formProfile.city" placeholder="Last name" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group label="Email" label-for="email">
                <b-form-input id="email" v-model="formProfile.email" placeholder="Email" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group label="Role" label-for="role">
                <b-form-select id="role" v-model="formProfile.role" :options="options.roles"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting" small />&nbsp;
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import moment from 'moment'

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
  computed: {
    totalRows() {
      return 1;
    },

  },

  watch: { },

  data: () => ({
    members: MockData,
    perPage: 10,
    currentPage: 1,
    fields: [
      {
        key: 'name',
        label: 'Name',
      },
      {
        key: 'email',
        label: 'Email',
      },
      {
        key: 'active',
        label: 'Status',
        formatter: (value, key, item) => {
          return value ? 'Active' : 'Deactive';
        }
      },
      {
        key: 'last_login_at',
        label: 'Last Login',
        formatter: (value, key, item) => {
          return moment.utc(value).local().fromNow()
        }
      },
      {
        key: 'actions',
        label: '',
      }
    ],
    isMain: true,
    isSubmitting: false,
    formProfile: {},
    options: {
      roles: [ 
        { value: null, text: 'Please select an option' },
        { value: 'Admin', text: 'Admin' },
        { value: 'Staff', text: 'Staff' },
      ],
    },
    isEditingRole: false,
  }),

  methods: {
    pageClickHandler(e, page) {
      console.log('-------- page at', page);
    },
    submitAddMemberForm() {
      console.log('---- add member form submit');
    },
    changeRole() {
      this.isEditingRole = true;
    },
    removeMember(index) {
      const temp = [...this.members];
      temp.splice(index, 1);
      this.members = temp;
    },
    changeActive(index) {
      const temp = [...this.members];
      temp[index].active = !temp[index].active;
      this.members = temp;
    }
  },

}
</script>

<style lang="scss" scoped>
.actions-btn {
  padding: 5px 10px;
  font-size: 12px;
  color: #fff;
  border-radius: 0.2rem;
  width: 80px;

  &.remove-btn {
    width: auto;
  }
}

#staff-members-table td {
  display: flex;
  align-items: center;
}
.h3 {
  margin: 0;
}
.link-btn {
  cursor: pointer;
}
.role-select {
  width: 100px;
  padding: 5px;
  height: auto;
  cursor: pointer;
}
</style>
