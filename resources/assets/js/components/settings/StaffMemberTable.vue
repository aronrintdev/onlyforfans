<template>
  <div>
    <b-table hover class="staff-members-table" :items="members" :fields="fields" show-empty >
      <template #empty="scope">
        <div class="p-5 text-center"><i>There is no active or invited accounts yet</i></div>
      </template>
      <template #cell(active)="data">
        <b-badge variant="info" v-if="data.item.pending">Invited</b-badge>
        <b-badge variant="success" v-else-if="data.item.active">Active</b-badge>
        <b-badge variant="secondary" v-else class="text-white">Deactive</b-badge>
      </template>
      <template #cell(actions)="data">
        <div class="text-right">
          <b-button class="actions-btn" variant="secondary" size="sm" @click="changeActive(data.index)"
            v-if="!data.item.pending && data.item.active">Deactivate</b-button>
          <b-button class="actions-btn" variant="success" size="sm" @click="changeActive(data.index)"
            v-if="!data.item.pending && !data.item.active">Activate</b-button>
          <b-button class="actions-btn remove-btn" variant="danger" size="sm" @click="removeMember(data.index)">
            <fa-icon :icon="['fas', 'trash']" size="sm" class="text-white" />
          </b-button>
        </div>
      </template>
    </b-table>
    <b-pagination
      v-if="has_pagination && metadata"
      v-model="metadata.current_page"
      :total-rows="metadata.total"
      :per-page="metadata.per_page"
      aria-controls="staff-members-table"
      v-on:page-click="pageClickHandler">
    </b-pagination>
    <b-modal
      v-model="showDeleteConfirmation"
      size="md"
      title="Delete Post"
    >
      <template #modal-title>
        {{ $t('delete.confirmation.title') }}
      </template>
      <div class="my-2 text-left" v-text="$t('delete.confirmation.message')" />
      <template #modal-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="showDeleteConfirmation=false">
            {{ $t('delete.confirmation.cancel') }}
          </b-btn>
          <b-btn class="px-3" variant="danger" @click="removeStaff">
            {{ $t('delete.confirmation.ok') }}
          </b-btn>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
  import moment from 'moment'

  export default {
    props: {
      items: { type: Array, default: []},
      metadata: { type: Object },
      has_pagination: { type: Boolean, default: true },
    },
  
    watch: {
      items(newV) {
        this.members = newV;
      }
    },

    mounted() {
      this.members = [...this.items];
    },

    data: () => ({
      members: [],
      fields: [{
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
            return value ? moment.utc(value).local().fromNow() : '';
          }
        },
        {
          key: 'actions',
          label: '',
        }
      ],
      isMain: true,
      showDeleteConfirmation: false,
      removingStaffIndex: null,
    }),

    methods: {
      pageClickHandler(e, page) {
        this.$emit('load', page);
      },
      removeMember(index) {
        this.showDeleteConfirmation = true;
        this.removingStaffIndex = index;
      },
      removeStaff() {
        const staff = this.members[this.removingStaffIndex];
        axios.delete(this.$apiRoute('staff.remove', staff.id))
          .then(() => {
            this.removingStaffIndex = null;
            this.showDeleteConfirmation = false;
            this.$emit('load');
          })
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

  .staff-members-table td {
    display: flex;
    align-items: center;
  }

  .h3 {
    margin: 0;
  }

  .role-select {
    width: 100px;
    padding: 5px;
    height: auto;
    cursor: pointer;
  }
</style>

<i18n lang="json5">
{
  "en": {
    "delete": {
      "confirmation": {
        "title": "Confirm remove",
        "message": "Are you sure you want to remove this staff?",
        "ok": "Confirm",
        "cancel": "Cancel"
      }
    },
  }
}
</i18n>
