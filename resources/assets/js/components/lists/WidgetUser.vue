<template>
  <b-card no-body class="background mb-5"> 
    <!-- widget: User -->
    <b-card-img :src="user.cover.filepath" alt="user.username" top></b-card-img>

    <b-card-body class="py-1">

      <div v-if="user.last_logged" class="last-seen">Last seen {{ moment(user.last_logged).format('MMM D') }}</div>

      <div class="banner-ctrl ">
        <b-dropdown no-caret right ref="bannerCtrls" variant="transparent" id="banner-ctrl-dropdown" class="tag-ctrl"> 
          <template #button-content>
            <fa-icon fixed-width icon="ellipsis-v" style="font-size:1.2rem; color:#fff" />
          </template>
          <b-dropdown-item v-clipboard="getTimelineUrl(user)">Copy link to profile</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item @click="doRestrict(user)">Restrict</b-dropdown-item>
          <b-dropdown-item @click="doBlock(user)">Block</b-dropdown-item>
          <b-dropdown-item @click="doReport(user)">Report</b-dropdown-item>
        </b-dropdown>
      </div>

      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug } }">
          <b-img thumbnail rounded="circle" class="w-100 h-100" :src="user.avatar.filepath" :alt="user.username" :title="user.name" />
        </router-link>
      </div>


      <div class="sharee-id">
        <b-card-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">{{ user.name }}</router-link>
          <fa-icon v-if="access_level==='premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">@{{ user.username }}</router-link>
        </b-card-sub-title>
      </div>

      <b-card-text v-if="notes" class="mt-2 mb-2">{{ notes }}</b-card-text>

      <b-card-text class="mb-2"><fa-icon fixed-width :icon="['far', 'star']" style="color:#007bff" /> Add to favorites</b-card-text>

      <b-button variant="primary"><fa-icon fixed-width :icon="['far', 'envelope']" /> Message</b-button>
      <b-button variant="warning"><fa-icon fixed-width :icon="['far', 'badge-percent']" /> Discount</b-button>
      <b-button variant="danger"><fa-icon fixed-width :icon="['far', 'ban']" /> Restrict</b-button>
      <b-button variant="primary" @click="showNotesModal"><fa-icon fixed-width :icon="['far', 'pencil']" /> {{ notesButtonCaption }}</b-button>
      <div class="mt-2 mb-2">
        <small v-if="access_level==='premium'" class="text-muted">Subscribed since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
        <small v-else class="text-muted">Following for free since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
      </div>

    </b-card-body>

    <b-modal id="modal-notes" class="modal-notes" v-model="isNotesModalVisible" size="md" title="Add Notes" >
      <div class="user-details">
        <div class="avatar-img">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">
            <b-img thumbnail rounded="circle" class="w-100 h-100" :src="user.avatar.filepath" :alt="user.username" :title="user.name" />
          </router-link>
        </div>
        <div class="sharee-id">
          <b-card-title class="mb-1">
            <router-link :to="{ name: 'timeline.show', params: { slug } }">{{ user.name }}</router-link>
            <fa-icon v-if="access_level==='premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
          </b-card-title>
          <b-card-sub-title class="mb-1">
            <router-link :to="{ name: 'timeline.show', params: { slug } }">@{{ user.username }}</router-link>
          </b-card-sub-title>
        </div>
      </div>
      <b-form-group class="flex-fill mt-3">
        <b-form-input v-model="notesInput" placeholder="Notes" />
      </b-form-group>
      <template #modal-footer>
        <b-button @click="hideNotesModal" type="cancel" variant="secondary">Cancel</b-button>
        <b-button @click="saveNote" :disabled="!notesInput" variant="primary">Save</b-button>
      </template>
    </b-modal>

  </b-card>
</template>

<script>
import { eventBus } from '@/app'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    user: null, // eg: the follower, or favorited user,
    slug: null,
    access_level: null,
    created_at: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user || !this.access_level || !this.created_at
    },

    notesButtonCaption() {
      if (this.notes) {
        return 'Edit Notes'
      }

      return 'Add Notes'
    },
  },

  data: () => ({
    moment: moment,
    isNotesModalVisible: false,
    notes: '',
    notesInput: '',
  }),

  methods: {
    // shareable in this context is the [shareables] record
    // shareable.sharee in this context is related user record
    doBlock(shareable) {
      console.log('doBlock() TODO'); // %TODO
    },

    doRestrict(shareable) {
      console.log('doRestrict() TODO'); // %TODO
    },

    doReport(shareable) {
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(user) {
      return route('spa.index', user.username)
    },

    showNotesModal() {
      this.isNotesModalVisible = true
    },

    hideNotesModal() {
      this.isNotesModalVisible = false
    },

    saveNote() {
      this.notes = this.notesInput
      this.hideNotesModal()
    },
  },

  mounted() { },
  created() { },
  components: { },

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}

#modal-notes {
  .user-details {
    display: flex;
    align-items: center;
  }

  h4 {
    font-size: 1.25rem;
  }

  .card-title a {
    color: #4a5568;
  }

  .card-subtitle a {
    color: #6e747d;
  }

  .avatar-img {
    width: 60px;
    height: 60px;
    margin-right: 10px;
  }
}
</style>

