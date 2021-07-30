<template>
  <div>
    <ResourceEditor
      ref="editor"
      v-model="role"
      model-name="Role"
      :protected-fields="['guard_name']"
      @save="saveRole"
    />

    <hr />

    <!-- Permissions List -->
    <div v-if="role">
      <div class="h3">Permissions</div>
      <b-table
        v-if="role.permissions"
        :fields="permissionsTableFields"
        :items="role.permissions"
        v-bind="tableProps"
      >
        <template #cell(actions)="data">
          <b-btn-group>
            <b-btn
              variant="danger"
              size="sm"
              v-b-tooltip.hover
              title="Remove this permission"
              @click="removePermission(data.item)"
            >
              <fa-icon icon="trash" />
            </b-btn>
          </b-btn-group>
        </template>
      </b-table>
      <!-- TODO: Remove Permission -->
      <!-- TODO: AddPermission -->
      <div class="add">
        <div v-if="addPermissionState === 'button'">
          <b-btn variant="success" block @click="addPermissionState = 'form'">
            <fa-icon icon="plus" /> Add new permission
          </b-btn>
        </div>
        <div v-else class="w-100 d-flex justify-content-between">
          <div class="row pr-3">
            <b-form-group
              label="Rule"
              description="The rule checked against, use * for any. If permission already exists it will be added"
              class="col"
            >
              <b-form-input v-model="newPermission.name" />
            </b-form-group>
            <b-form-group
              label="Name"
              description="Readable display name of the permission (optional)"
              class="col"
            >
              <b-form-input v-model="newPermission.display_name" />
            </b-form-group>
            <b-form-group
              label="Description"
              description="Readable description of rule (optional)"
              class="col"
            >
              <b-form-input v-model="newPermission.description" />
            </b-form-group>
          </div>

          <div class="d-flex">
            <b-btn-group class="my-auto">
              <b-btn
                @click="addPermission"
                variant="success"
                v-b-tooltip.hover
                title="Add this permission"
              >
                <fa-icon icon="check" />
              </b-btn>
              <b-btn variant="danger" @click="addPermissionCancel" v-b-tooltip.hover title="Cancel">
                <fa-icon icon="times" />
              </b-btn>
            </b-btn-group>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * View / Edit specific role
 */

import ResourceEditor from '../../common/ResourceEditor'

const blankPermission = {
  name: '',
  display_name: '',
  description: '',
}

export default {
  components: {
    ResourceEditor,
  },
  props: {
    roleId: { type: [Number, String] },
    value: { type: Boolean, default: false },
  },
  data: () => ({
    role: null,
    state: 'loading', // loading | viewing | editing | error
    addPermissionState: 'button', // button | form | submitting | error
    newPermission: blankPermission,
    permissionsTableFields: [
      {
        key: 'name',
        label: 'Rule',
      },
      {
        key: 'display_name',
        label: 'Name',
      },
      {
        key: 'description',
        label: 'Description',
      },
      {
        key: 'actions',
        label: 'Actions',
      },
    ],
    tableProps: {
      'head-variant': 'light',
      small: true,
      responsive: true,
      hover: true,
      striped: true,
      outlined: true,
      'primary-key': 'id',
    },
  }),
  methods: {
    /** Add new permission to role */
    addPermission() {
      this.addPermissionState = 'submitting'
      this.axios
        .post(this.$apiRoute('admin.role.permissions.assign', this.role), {
          permissions: [this.newPermission],
        })
        .then((result) => {
          if (result.statusText === 'OK') {
            this.addPermissionState = 'button'
            this.newPermission = blankPermission
            this.role = result.data
          }
        })
        .catch((error) => {
          this.$log.error(error)
          this.addPermissionState = 'error'
        })
    },

    /** Cancel permission adding */
    addPermissionCancel() {
      this.addPermissionState = 'button'
      this.newPermission = blankPermission
    },

    /** Remove Permission from role */
    removePermission(permission) {
      this.axios.post(this.$apiRoute('admin.role.permissions.remove', this.role), {
        permissions: [permission],
      })
      .then(response => {
        if (response.statusText === 'OK') {
          this.role = response.data
        }
      })
      .catch(error => {
        this.$log.error(error)
      })
    },

    saveRole({ callback }) {
      this.axios
        .put(this.$apiRoute('admin.role.update', this.role), this.role)
        .then((result) => {
          this.$log.debug('saveRole', { result })
          if (result.statusText === 'OK') {
            callback()
          } else {
            callback({ error: true })
          }
        })
        .catch((error) => {
          this.$log.error(error)
          callback({ error })
        })
    },

    /** Load Role */
    load() {
      if (this.roleId) {
        this.state = 'loading'
        this.axios
          .get(this.$apiRoute('admin.role.show', { role: this.roleId }))
          .then((response) => {
            if (response.statusText === 'OK') {
              this.role = response.data
            } else {
              this.state = 'error'
            }
          })
          .catch((error) => {
            this.$log.error(error)
            this.state = 'error'
          })
      }
    },
  },
  watch: {
    roleId() {
      this.load()
    },
  },
  mounted() {
    this.load()
  },
}
</script>
