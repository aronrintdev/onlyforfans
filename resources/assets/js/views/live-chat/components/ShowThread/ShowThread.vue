<template>
  <div v-if="!isLoading" class="h-100 d-flex flex-column" :key="this.id">

    <section class="chatthread-header pt-3" :class="{ mobile: mobile }">
      <div class="d-flex align-items-center">
        <b-btn variant="link" class="" @click="onBackClicked">
          <fa-icon :icon="['fas', 'arrow-left']" class="fa-lg" />
        </b-btn>
        <p class="m-0">
          <AvatarWithStatus :user="participant" :thumbnail="false" noLink />
        </p>
        <OptionsDropdown
          :participant="participant"
          :favorited="isFavorite"
          :muted="!!isMuted"
          :hasNotes="!!notes"
          @tip="tip"
          @addNotes="addNotes"
          @toggleMute="toggleMute"
          @toggleFavorite="toggleFavorite"
        />
      </div>
      <div class="d-flex align-items-center">
        <b-btn variant="link" class="text-nowrap" @click="toggleFavorite">
          <fa-icon :icon="isFavorite ? [ 'fas', 'star' ] : ['far', 'star']" size="lg" class="mr-1" />
          <span v-if="!mobile" v-text="$t('buttons.favorite')" />
        </b-btn>
        <div class="text-muted">|</div>
        <b-btn
          variant="link"
          class="text-nowrap"
          v-b-tooltip:hover
          :title="$t('tooltip.notifications')"
          @click="toggleMute"
        >
          <fa-icon
            v-if="!isMuted"
            :icon="['far', 'bell']"
            fixed-width
            size="lg"
            title="Notifications ON"
            class="mr-1"
          />
          <fa-icon
            v-if="isMuted"
            :icon="['far', 'bell-slash']"
            fixed-width
            size="lg"
            title="Notifications OFF"
            class="muted mr-1"
          />
          <span v-if="!mobile" v-text="$t('buttons.notifications')" />
        </b-btn>
        <div class="text-muted">|</div>
        <b-btn
          variant="link"
          class="text-nowrap"
          v-b-tooltip:hover
          :title="$t('tooltip.gallery')"
          @click="toggleGallery"
        >
          <fa-icon :icon="showGallery ? ['fas', 'image'] : ['far', 'image']" size="lg" class="mr-1" />
          <span v-if="!mobile" v-text="$t('buttons.gallery')" />
        </b-btn>
        <div class="text-muted">|</div>
        <b-btn
          variant="link"
          class="text-nowrap"
          v-b-tooltip:hover
          :title="$t('tooltip.tip')"
          @click="tip"
        >
          <fa-icon icon="dollar-sign" fixed-width size="lg" class="mr-1" />
          <span v-if="!mobile" v-text="$t('buttons.tip')" />
        </b-btn>
        <div class="text-muted">|</div>
        <SearchInput v-model="searchQuery" size="lg" />
      </div>
    </section>

    <section v-if="vaultSelectionOpen" key="vaultSelect" class="vault-selection">
      <VaultSelector @close="vaultSelectionOpen = false" />
    </section>
    <section v-else-if="showGallery" key="gallery" class="gallery flex-fill">
      <Gallery :threadId="id" @close="showGallery = false" />
    </section>
    <MessageDisplay
      v-else
      :items="searchResults === null ? chatmessages: searchResults"
      :loading="moreLoading"
      :isLastPage="isLastPage"
      :isSearch="searchResults !== null"
      :searchQuery="searchQuery"
      key="messages"
      class="flex-fill"
      @endVisible="endVisible"
    />

    <TypingIndicator :threadId="id" />

    <MessageForm
      v-if="!showGallery"
      :session_user="session_user"
      :chatthread_id="id"
      :vaultOpen="vaultSelectionOpen"
      class="message-form"
      :class="{ mobile: mobile }"
      @sendMessage="addTempMessage"
      @toggleVaultSelect="vaultSelectionOpen = !vaultSelectionOpen"
    />

    <b-modal id="modal-notes" hide-footer body-class="p-0" v-model="isNotesModalVisible" size="md" :title="modalTitle" >
      <AddNotes
        :timeline="timeline"
        :notes="notes"
        :onClose="hideNotesModal"
        :onUpdate="updateNotes"
      />
    </b-modal>

  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread/ShowThread.vue
 */
import { eventBus } from '@/eventBus'
import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'

import AvatarWithStatus from '@components/user/AvatarWithStatus'
import AddNotes from '@components/common/AddNotes'
import Gallery from './Gallery'
import MessageDisplay from '@views/live-chat/components/MessageDisplay'
import MessageForm from '@views/live-chat/components/NewMessageForm'
import OptionsDropdown from './OptionsDropdown'
import SearchInput from '@components/common/search/HorizontalOpenInput'
import TypingIndicator from './TypingIndicator'
import VaultSelector from './VaultSelector'

export default {
  name: 'ShowThread',

  components: {
    AvatarWithStatus,
    Gallery,
    MessageDisplay,
    MessageForm,
    OptionsDropdown,
    SearchInput,
    TypingIndicator,
    VaultSelector,
    AddNotes,
  },

  props: {
    timeline: null,
    id: null, // the chatthread PKID
    currentNotes: null,
  },

  computed: {
    ...Vuex.mapState(['session_user', 'mobile']),
    ...Vuex.mapState('messaging', [ 'threads' ]),

    isLoading() {
      return !this.session_user || !this.participant || !this.id || !this.chatmessages
    },

    isFavorite() {
      return this.thread ? this.thread.is_favorite : false
    },

    channelName() {
      return `chatthreads.${this.id}`
    },

    participant() {
      if (!this.thread) {
        return null
      }
      // Find first participant that is not the session user
      return _.find(this.thread.participants, participant => participant.id !== this.session_user.id)
    },

    thread() {
      return this._thread()(this.id)
    },

    modalTitle() {
      if (this.notes) {
        return 'Edit Notes'
      } else {
        return 'Add Notes'
      }
    }

  },

  data: () => ({

    moment: moment,

    chatmessages: [],
    isMuted: false,
    meta: null,
    perPage: 10,
    currentPage: 1,

    isLastPage: false,
    moreLoading: false,
    isEndVisible: false,

    searchQuery: '',
    // Search Loading is object keyed by the query being searched. This makes it so that we know if multiple searches
    // are loading if the server response is slow
    searchLoading: {},
    searchResults: null,

    debounceAmount: 500, // 0.5s

    showGallery: false,

    vaultSelectionOpen: false,

    isNotesModalVisible: false,
    notes: null,
  }), // data

  created() {
    this.search = _.debounce(this._search, this.debounceAmount)
    this.notes = this.currentNotes
  },

  mounted() {
    //console.log(`live-chat/components/ShowThread::mounted() - calling getChatmessages with id ${this.id}`)
    this.getMuteStatus(this.id)
    this.getChatmessages(this.id)

    this.getThread(this.id).then(response => {
      this.$nextTick(() => {
        this.computeThread()
      })
    }).catch(error => eventBus.$emit('error', { error, message: this.$t('error')}))

    this.markRead(this.id)
    //this.$log.debug('ShowThread Mounted', { channelName: this.channelName })
    this.$echo.join(this.channelName)
      .listen('.chatmessage.sent', e => {
        //this.$log.debug('Event Received: .chatmessage.sent', { e })
        this.addMessage(e.chatmessage)
      })
      .listenForWhisper('sendMessage', e => {
        //this.$log.debug('Whisper Received: sendMessage', { e })
        this.addTempMessage(e.message)
      })
  },

  methods: {
    ...Vuex.mapActions(['getUnreadMessagesCount']),
    ...Vuex.mapActions('messaging', ['getThread']),
    ...Vuex.mapMutations('messaging', [ 'UPDATE_THREAD' ]),
    ...Vuex.mapGetters('messaging', { _thread: 'thread' }),

    computeThread() {
      this.$forceCompute('thread')
      this.$forceCompute('participant')
      this.$forceCompute('isFavorite')
    },

    /**
     * Add official message from db, overwrite temp message if necessary
     */
    addMessage(message) {
      //this.$log.debug('ShowThread addMessage', { message })
      var replaced = false
      for (var i in this.chatmessages) {
        if (
          this.chatmessages[i].temp &&
          this.chatmessages[i].sender_id === message.sender_id &&
          this.chatmessages[i].mcontent === message.mcontent
        ) {
          //this.$log.debug('ShowThread addMessage replaced message', { i, message: this.chatmessages[i] })
          Vue.set(this.chatmessages, i, message)
          replaced = true
          break;
        }
      }
      //this.$log.debug('ShowThread addMessage', { replaced })
      if (!replaced) {
        this.chatmessages = [
          message,
          ...this.chatmessages,
        ]
      }
    },

    /**
     * Quickly add a temp message to data set while official one is processed in db
     */
    addTempMessage(message) {
      //this.$log.debug('ShowThread addTempMessage', { message })
      this.chatmessages = [
        { id: moment().valueOf(), temp: true, ...message },
        ...this.chatmessages,
      ]
    },

    endVisible(isVisible) {
      //this.$log.debug('endVisible', { isVisible })
      //console.log(`live-chat/components/ShowThread::endVisible()`)
      this.isEndVisible = isVisible
      if (isVisible && !this.moreLoading && !this.isLastPage) {
        this.loadNextPage()
      }
    },

    loadNextPage() {
      //console.log(`live-chat/components/ShowThread::loadNextPage() - calling getChatmessages with no id `)
      this.currentPage += 1
      this.moreLoading = true
      this.getChatmessages(this.id)
    },

    isDateBreak(cm, idx) {
      if (idx === this.chatmessages.length - 1) {
        return true
      }
      const current = moment(this.chatmessages[idx].created_at);
      const next = moment(this.chatmessages[idx + 1].created_at,);
      return !current.isSame(next, 'date')
    },

    async getChatmessages(chatthreadID) {
      const params = {
        page: this.currentPage,
        take: this.perPage,
        chatthread_id: chatthreadID,
      }
      //console.log('live-chat/components/ShowThread::getChatmessages() - chatmessages.index', { params })
      const response = await axios.get( this.$apiRoute('chatmessages.index'), { params } )

      // Filter out any messages that we already have
      const newMessages = _.filter(response.data.data, incoming => (
        _.findIndex(this.chatmessages, message => message.id === incoming.id) === -1
      ))

      //this.$log.debug('getChatmessages', { newMessages })

      this.chatmessages = [
        ...this.chatmessages,
        ...newMessages,
      ]
      this.meta = response.meta
      this.moreLoading = false
      if ( response.data.meta.last_page === response.data.meta.current_page ) {
        this.isLastPage = true
      }
    },

    async markRead(chatthreadID) {
      await axios.post(this.$apiRoute('chatthreads.markRead', chatthreadID))

      // reload total unread count
      this.getUnreadMessagesCount()
    },

    async getMuteStatus(chatthreadID) {
      try {
        const response = await axios.get( this.$apiRoute('chatthreads.getMuteStatus', chatthreadID) )
        this.isMuted = response.data.is_muted;
      } catch (err) {
        if (err.response.status === 403) {
          console.error('Cannot get mute status of the thread because user doesn\'t have permission!')
          // FIXME: remove or uncomment. commented for now, since it's disruptive
          // this.$root.$bvToast.toast('You do not have permission to view this thread!', {
          //   toaster: 'b-toaster-top-center',
          //   title: 'Error',
          //   variant: 'danger',
          // })
        }
      }
    },

    onBackClicked() {
      if (this.showGallery) {
        this.showGallery = false
        return
      }
      this.$router.push({name: 'chatthreads.dashboard'})
    },

    toggleGallery() {
      this.showGallery = !this.showGallery
    },

    async toggleMute() {
      try {
        await axios.post(this.$apiRoute('chatthreads.toggleMute', this.id), { is_muted: !this.isMuted })
        this.isMuted = !this.isMuted;
      } catch (err) {
        if (err.response.status === 403) {
          this.$root.$bvToast.toast('You do not have permission to update this thread!', {
            toaster: 'b-toaster-top-center',
            title: 'Error',
            variant: 'danger',
          })
        }
      }
    },

    tip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: {
          resource: this.timeline,
          resource_type: 'timelines',
        },
      })
    },

    addNotes() {
      this.isNotesModalVisible = true
    },

    hideNotesModal() {
      this.isNotesModalVisible = false
    },

    updateNotes(notes) {
      this.notes = notes
    },

    toggleFavorite() {
      const isFavorite = this.thread.is_favorite
      this.UPDATE_THREAD({ ...this.thread, is_favorite: isFavorite ? false : true })
      this.$nextTick(() => {
        this.computeThread()
      })
      if (isFavorite) {
        this.axios.post(this.$apiRoute('favorites.remove'), {
          favoritable_id: this.id,
          favoritable_type: 'chatthreads',
        })
      } else {
        this.axios.post(this.$apiRoute('favorites.store'), {
          favoritable_id: this.id,
          favoritable_type: 'chatthreads',
        })
      }
    },

    /**
     * Do search request on server
     */
    _search(query) {
      if (this.searchLoading[query]) {
        return
      }
      this.searchLoading[query] = true
      this.axios.get(this.$apiRoute('chatmessages.search'), { params: { q: query, chatthread: this.id } })
        .then(response => {
          this.searchResults = response.data.data
        })
        .catch(error => {
          eventBus.$emit(error, { error, message: this.$t('search.error') })
        })
        .finally(() => {
          // Remove loading for this query
          delete this.searchLoading[query]
        })
    },

    doSomething() {
      // stub placeholder for impl
    },

  }, // methods

  watch: {

    debounceAmount(value) {
      this.search = _.debounce(this._search, value)
    },

    id (newValue, oldValue) {
      if ( newValue && (newValue !== oldValue) ) {
        //console.log(`live-chat/components/ShowThread::watch(id) - calling getChatmessages with id ${newValue}`)
        this.getChatmessages(newValue)
        this.markRead(newValue)
        this.getMuteStatus(newValue)
      }
    },

    searchQuery(value) {
      if (value === '') {
        this.searchResults = null
        return
      }
      this.search(value)
    },
  }, // watch

}
</script>

<style lang="scss" scoped>
::v-deep.btn-link:hover {
  text-decoration: none;
}
::v-deep.btn:focus, ::v-deep.btn.focus, ::v-deep.btn:active {
  box-shadow: none;
  text-decoration: none;
}

.chatthread-header {
  &.mobile {
    position: sticky;
    top: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    padding: 0.5rem;
    z-index: 5;
  }
}

.message-form {
  &.mobile {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    padding: 0.5rem;
    z-index: 5;
  }
}

.scroll-wrapper {
  overflow-y: auto;
  overflow-x: hidden;
}

.vault-selection {
  height: 100%;
  width: 100%;
}
.gallery {
  height: 100%;
  width: 100%;
}


.muted {
  opacity: .5;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "error": "An error has occurred while loading this chat thread. Please try again later",
    "buttons": {
      "favorite": "Favorite",
      "notifications": "Notifications",
      "gallery": "Gallery",
      "tip": "Tip",
    },
    "tooltip": {
      "notifications": "Activate or Deactivate Notifications for this Chat Thread",
      "gallery": "View Gallery of Media",
      "tip": "Send Tip"
    }
  }
}
</i18n>
