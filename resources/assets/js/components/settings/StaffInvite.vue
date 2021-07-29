<template>
  <b-card class="mb-3">
    <b-card-text v-if="!isLoading">
      <a class="text-primary text-decoration-none link-btn" @click="goBack">
        <fa-icon :icon="['far', 'arrow-left']" />
        <span class="px-1">{{ creator_id == null ? 'Managers' : 'Staff members' }}</span>
      </a>
      <div class="d-flex align-items-center my-4">
        <div class="h3 text-center" v-if="creator_id == null">Invite Manager</div>
        <div class="h3 text-center" v-else>Invite Staff Member</div>
      </div>
      <b-form @submit.prevent="submitForm">
        <b-row class="mb-3">
          <b-col>
            <b-form-group label="First name" label-for="first_name">
              <b-form-input id="first_name" v-model="formData.first_name" placeholder="First name" ></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Last name" label-for="last_name">
              <b-form-input id="last_name" v-model="formData.last_name" placeholder="Last name" ></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row class="mb-3">
          <b-col>
            <b-form-group label="Email" label-for="email">
              <b-form-input id="email" v-model="formData.email" type="email" placeholder="Email" ></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row class="mb-3" v-if="creator_id">
          <b-col>
            <b-form-group label="Permissions">
              <b-form-checkbox-group
                v-model="formData.permissions"
              >
                <div class="permission-group" v-for="(vaules, group) in permissionGroups" :key="group">
                  <div class="group-name">- {{ group }}</div>
                  <div class="permission" v-for="permission in vaules" :key="permission.id">
                    <b-form-checkbox class="mb-3 checkbox" :value="permission.id">
                      {{ permission.display_name }}
                    </b-form-checkbox>
                  </div>
                </div>
              </b-form-checkbox-group>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row class="mt-3">
          <b-col>
            <div class="w-100 d-flex justify-content-end">
              <b-button :disabled="isSubmitting" class="w-25 ml-3" type="submit" variant="primary">
                <b-spinner v-if="isSubmitting" small />&nbsp;
                Send invite
              </b-button>
            </div>
          </b-col>
        </b-row>

      </b-form>
    </b-card-text>
  </b-card>
</template>

<script>
  import { groupBy } from 'lodash';

  export default {
    props: {
      items: { type: Array, default: () => ([])},
      session_user: null,
      creator_id: { type: String, default: null }
    },

    created() {
      axios.get(this.$apiRoute('staff.permissions'))
        .then(response => {
          const permissions = response.data.map(p => {
            p.category = p.name.split('.')[0];
            return p;
          });
          this.permissionGroups = groupBy(permissions, 'category');
          this.isLoading = false;
        })
    },

    data: () => ({
      isSubmitting: false,
      formData: {
        permissions: [],
      },
      permissionGroups: [],
      isLoading: true,
    }),

    methods: {
      goBack() {
        this.$emit('exit');
      },
      submitForm() {
        this.isSubmitting = true;
        // Call Invite REST API
        const formData = {
          ...this.formData,
          pending: 1,
          role: this.creator_id ? 'staff' : 'manager',
          name: `${this.formData.first_name} ${this.formData.last_name}`,
          creator_id: this.creator_id,
        }
        this.axios.post(this.$apiRoute('users.sendStaffInvite'), formData).then(() => {
          this.$emit('send');
          this.isSubmitting = false;
        })
        .catch(error => {
          console.error(error.message);
          this.isSubmitting = false;
        })
      }
    },

  }
</script>

<style lang="scss" scoped>
.link-btn {
  cursor: pointer;
}
.permission-group {
  padding: 0 0.5em 1em 0.5em;
  
  .group-name {
    font-size: 15px;
    color: #666;
    margin-bottom: 0.5em;
  }
  .permission {
    display: inline-block;
    margin: 0 2em -0.5em 0;
  }
}
</style>

<style lang="scss">
.permission-group {
  .permission {
    .checkbox label {
      font-size: 15px;
      display: flex;
      align-items: center;
    }
  }
}
</style>