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
          <b-dropdown-item disabled @click="doRestrict(user)">Restrict</b-dropdown-item>
          <b-dropdown-item disabled @click="doBlock(user)">Block</b-dropdown-item>
          <b-dropdown-item disabled @click="doReport(user)">Report</b-dropdown-item>
        </b-dropdown>
      </div>

      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug } }">
          <b-img-lazy thumbnail rounded="circle" class="w-100 h-100" :src="user.avatar.filepath" :alt="user.username" :title="user.name" />
        </router-link>
        <OnlineStatus :user="user" size="lg" :textVisible="false" />
      </div>


      <div class="sharee-id">
        <b-card-title class="mb-1 subscriber-card">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">{{ user.name }}</router-link>
          <span v-if="access_level==='premium'" class="subscriber">
            <b-badge variant='info'>
              Subscriber
            </b-badge>
          </span>
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">@{{ user.username }}</router-link>
        </b-card-sub-title>
        <OnlineStatus :user="user" :indicatorVisible="false" />
      </div>

      <b-card-text v-if="notes" class="mt-2 mb-2"><pre>{{ notes.notes || '' }}</pre></b-card-text>

      <b-card-text class="mb-2 clickable" @click="toggleFavorite">
        <fa-icon v-if="isFavoritedByMe" fixed-width :icon="['fas', 'star']" style="color:#007bff" />
        <fa-icon v-else fixed-width :icon="['far', 'star']" style="color:#007bff" />
        Add to favorites
      </b-card-text>

      <b-button @click="redirectToMessages()" class="mb-1" variant="primary">Message</b-button>
      <b-button class="mb-1" disabled variant="primary">Discount</b-button>
      <b-button class="mb-1" disabled variant="primary">Restrict</b-button>
      <b-button class="mb-1" variant="primary" @click="showNotesModal">{{ notesButtonCaption }}</b-button>
      <div class="mt-2 mb-2">
        <small v-if="access_level==='premium'" class="text-muted">Subscribed since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
        <small v-else class="text-muted">Following for free since {{ moment(created_at).format('MMM DD, YYYY') }}</small>
      </div>

    </b-card-body>

    <b-modal id="modal-notes" hide-footer body-class="p-0" v-model="isNotesModalVisible" size="md" :title="modalTitle" >
      <AddNotes
        :timeline="timeline"
        :notes="notes"
        :onClose="hideNotesModal"
        :onUpdate="updateNotes"
      />
    </b-modal>

  </b-card>
</template>

<script>
import { eventBus } from '@/eventBus'
//import { DateTime } from 'luxon'
import moment from 'moment'
import AddNotes from '@components/common/AddNotes'
import OnlineStatus from '@components/common/OnlineStatus'

export default {

  props: {
    session_user: null,
    timeline_id: null,
    user: null, // eg: the follower, or favorited user,
    slug: null,
    access_level: null,
    created_at: null,
    is_favorited: null,
    shareable_id: null,
    current_notes: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user || !this.access_level || !this.created_at
    },

    notesButtonCaption() {
      if (this.notes) {
        return 'Notes'
      }

      return 'Notes'
    },

    timeline() {
      return {
        id: this.timeline_id,
        name: this.user.name,
        avatar: this.user.avatar,
        slug: this.slug,
      }
    },

    modalTitle() {
      if (this.current_notes) {
        return 'Edit Notes'
      } else {
        return 'Add Notes'
      }
    }
  },

  data: () => ({
    moment: moment,
    isNotesModalVisible: false,
    notes: null,

    isFavoritedByMe: false, // is timeline/feed a favorite
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

    updateNotes(notes) {
      this.notes = notes
    },

    async toggleFavorite() {
      let response
      if (this.isFavoritedByMe) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline_id,
        })
        this.isFavoritedByMe = false
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline_id,
        })
        this.isFavoritedByMe = true
      }
    },

    async redirectToMessages() {
      const payload = {
        originator_id: this.session_user.id,
        participant_id: this.user.id,
      }
      const response = await axios.post( this.$apiRoute('chatthreads.findOrCreateDirect'), payload)
      if (response.data.chatthread) {
        this.$router.push({ name: 'chatthreads.show', params: { id: response.data.chatthread.id }})
      }
    },

  }, // methods

  mounted() {
    this.isFavoritedByMe = this.is_favorited;
    this.notes = this.current_notes;
  },
  created() { },
  components: {
    OnlineStatus,
    AddNotes,
  },

}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}

pre {
  font-family: inherit;
  font-size: inherit;
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

  button {
    width: 6rem;
  }

  .subscriber {
    margin-left: 15px;
    font-size: 18px;
  }

  .subscriber-card {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
  }
}
</style>

