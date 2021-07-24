<template>
  <div v-if="!isLoading" class="h-100 d-flex flex-column">

    <section class="chatthread-header">
      <div class="d-flex align-items-center">
        <b-button variant="link" class="" @click="onBackClicked">
          <fa-icon :icon="['fas', 'arrow-left']" class="fa-lg" />
        </b-button>
        <p class="m-0"><strong>{{ participant.username }}</strong></p>
        <b-dropdown id="ctrls-participants" variant="link" size="sm" class="ml-auto" right no-caret>
          <template #button-content>
            <fa-icon :icon="['fas', 'ellipsis-h']" class="fa-sm" />
          </template>
          <b-dropdown-item>Add to / remove from list</b-dropdown-item>
          <b-dropdown-item>Give user a discount</b-dropdown-item>
          <b-dropdown-item>Delete conversation</b-dropdown-item>
          <b-dropdown-item>Rename @{{ participant.username }}</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item>Hide chat</b-dropdown-item>
          <b-dropdown-item>Mute notifications</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item>Restrict @{{ participant.username }}</b-dropdown-item>
          <b-dropdown-item>Block @{{ participant.username }}</b-dropdown-item>
          <b-dropdown-item>Report @{{ participant.username }}</b-dropdown-item>
        </b-dropdown>
      </div>
      <div class="d-flex align-items-center">
        <p class="my-0 mx-2 text-nowrap">Last Seen</p>
        <div>|</div>
        <b-button variant="link" class="" @click="doSomething">
          <fa-icon :icon="['far', 'star']" class="fa-lg" />
        </b-button>
        <div>|</div>
        <b-button variant="link" class="" @click="toggleMute">
          <fa-icon v-if="!isMuted" :icon="['far', 'bell']" fixed-width class="fa-lg" title="Notifications ON" />
          <fa-icon v-if="isMuted" :icon="['far', 'bell-slash']" fixed-width class="fa-lg muted" title="Notifications OFF" />
        </b-button>
        <div>|</div>
        <b-button variant="link" class="" @click="toggleGallery" v-b-tooltip:hover :title="$t('tooltips.gallery')">
          <fa-icon :icon="showGallery ? ['fas', 'image'] : ['far', 'image']" class="fa-lg" />
        </b-button>
        <div>|</div>
        <b-btn variant="link" @click="tip" v-b-tooltip:hover :title="$t('tooltips.tip')">
          <fa-icon icon="dollar-sign" fixed-width />
        </b-btn>
        <div>|</div>
        <SearchInput v-model="searchQuery" />
      </div>
    </section>

    <hr />

    <transition-group name="quick-fade" mode="out-in" class="flex-fill scroll-wrapper">
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
    </transition-group>

    <TypingIndicator :threadId="id" />

    <MessageForm
      v-if="!showGallery"
      :session_user="session_user"
      :chatthread_id="id"
      :vaultOpen="vaultSelectionOpen"
      @sendMessage="addTempMessage"
      @toggleVaultSelect="vaultSelectionOpen = !vaultSelectionOpen"
    />

  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread.vue
 */
import { eventBus } from '@/eventBus'
import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'


import MessageForm from '@views/live-chat/components/NewMessageForm'
import SearchInput from '@components/common/search/HorizontalOpenInput'
import TypingIndicator from './TypingIndicator'
import VaultSelector from './VaultSelector'
import MessageDisplay from './MessageDisplay'
import Gallery from './Gallery'

export default {
  name: 'ShowThread',

  components: {
    Gallery,
    MessageDisplay,
    MessageForm,
    SearchInput,
    TypingIndicator,
    VaultSelector,
  },

  props: {
    session_user: null,
    participant: null,
    timeline: null,
    id: null, // the chatthread PKID
  },

  computed: {

    isLoading() {
      return !this.session_user || !this.participant || !this.id || !this.chatmessages
    },

    channelName() {
      return `chatthreads.${this.id}`
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

  }), // data

  created() {
    this.search = _.debounce(this._search, this.debounceAmount)
  },

  mounted() {
    this.getMuteStatus(this.id)
    this.getChatmessages(this.id)
    this.markRead(this.id)
    this.$log.debug('ShowThread Mounted', { channelName: this.channelName })
    this.$echo.join(this.channelName)
      .listen('.chatmessage.sent', e => {
        this.$log.debug('Event Received: .chatmessage.sent', { e })
        this.addMessage(e.chatmessage)
      })
      .listenForWhisper('sendMessage', e => {
        this.$log.debug('Whisper Received: sendMessage', { e })
        this.addTempMessage(e.message)
      })
  },

  methods: {
    ...Vuex.mapActions(['getUnreadMessagesCount']),

    /**
     * Add official message from db, overwrite temp message if necessary
     */
    addMessage(message) {
      this.$log.debug('ShowThread addMessage', { message })
      var replaced = false
      for (var i in this.chatmessages) {
        if (
          this.chatmessages[i].temp &&
          this.chatmessages[i].sender_id === message.sender_id &&
          this.chatmessages[i].mcontent === message.mcontent
        ) {
          this.$log.debug('ShowThread addMessage replaced message', { i, message: this.chatmessages[i] })
          Vue.set(this.chatmessages, i, message)
          replaced = true
          break;
        }
      }
      this.$log.debug('ShowThread addMessage', { replaced })
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
      this.$log.debug('ShowThread addTempMessage', { message })
      this.chatmessages = [
        { id: moment().valueOf(), temp: true, ...message },
        ...this.chatmessages,
      ]
    },

    endVisible(isVisible) {
      this.$log.debug('endVisible', { isVisible })
      this.isEndVisible = isVisible
      if (isVisible && !this.moreLoading && !this.isLastPage) {
        this.loadNextPage()
      }
    },

    loadNextPage() {
      this.currentPage += 1
      this.moreLoading = true
      this.getChatmessages()
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
      const response = await axios.get( this.$apiRoute('chatmessages.index'), { params } )

      // Filter out any messages that we already have
      const newMessages = _.filter(response.data.data, incoming => (
        _.findIndex(this.chatmessages, message => message.id === incoming.id) === -1
      ))

      this.$log.debug('getChatmessages', { newMessages })

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

  //    setFollowForFree: function(userId) {
  //      this.axios.patch(`/users/${userId}/settings`, {
  //        is_follow_for_free: true,
  //      }).then(() => {
  //        this.selectedUser = {
  //          ...this.selectedUser,
  //          profile: {
  //            ...this.selectedUser.profile,
  //            is_follow_for_free: true,
  //          }
  //        };
  //      });
  //    },
  //    openMessagePriceModal: function() {
  //      this.tempMessagePrice = undefined;
  //      this.$refs['message-price-modal'].show();
  //    },
  //    closeMessagePriceModal: function() {
  //      this.tempMessagePrice = undefined;
  //      this.$refs['message-price-modal'].hide();
  //    },
  //    saveMessagePrice: function() {
  //      this.messagePrice = this.tempMessagePrice;
  //      this.$refs['message-price-modal'].hide();
  //      console.log('messagePrice:', this.messagePrice);
  //    },
  //    onMessagePriceChange: function(val) {
  //      if (val < 5) {
  //        this.tempMessagePrice = 5;
  //      } else {
  //        this.tempMessagePrice = val;
  //      }
  //    },
  //    clearMessagePrice: function() {
  //      this.messagePrice = undefined;
  //    },
  //    openUnsendMessageModal: function(messageId) {
  //      this.$refs['unsend-message-modal'].show();
  //      this.unsendTipMessageId = messageId;
  //    },
  //    closeUnsendMessageModal: function() {
  //      this.unsendTipMessageId = undefined;
  //      this.$refs['unsend-message-modal'].hide();
  //    },
  //    unsendTipMessage: function() {
  //      const self = this;
  //      if (this.unsendTipMessageId) {
  //        this.axios.delete(`/chat-messages/${this.$route.params.id}/threads/${this.unsendTipMessageId}`)
  //          .then(() => {
  //            const idx = self.originMessages.findIndex(message => message.id === self.unsendTipMessageId);
  //            self.originMessages.splice(idx, 1);
  //            self.originMessages = _.cloneDeep(self.originMessages);
  //            self.groupMessages();
  //            self.closeUnsendMessageModal();
  //          });
  //      }
  //    },
  //    openMessagePriceConfirmModal: function(value) {
  //      this.confirm_message_price = value;
  //      this.$refs['confirm-message-price-modal'].show();
  //    },
  //    closeMessagePriceConfirmModal: function() {
  //      this.$refs['confirm-message-price-modal'].hide();
  //    },
  //    onCheckReturnKey: function(e) {
  //      if (e.ctrlKey && e.keyCode === 13) {
  //        this.sendMessage();
  //      }
  //    },
  //    clearMessages: function (receiver) {
  //      this.axios.delete(`/chat-messages/${receiver.id}`)
  //        .then(() => {
  //          const idx = this.users.findIndex(user => user.profile.id === receiver.id);
  //          this.users.splice(idx, 1);
  //          this.$router.push('/messages');
  //        })
  //    },
  //    setLikeMessage: function(message) {
  //      this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/like`)
  //        .then(() => {
  //          const newMessages = [...this.messages];
  //          newMessages.forEach(thread => {
  //            const idx = thread.messages.findIndex(m => m.id === message.id);
  //            if (idx > -1) {
  //              thread.messages[idx].is_like = 1;
  //            }
  //          });
  //          this.messages = newMessages;
  //        })
  //    },
  //    setUnlikeMessage: function(message) {
  //      this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/unlike`)
  //        .then(() => {
  //          const newMessages = [...this.messages];
  //          newMessages.forEach(thread => {
  //            const idx = thread.messages.findIndex(m => m.id === message.id);
  //            if (idx > -1) {
  //              thread.messages[idx].is_like = 0;
  //            }
  //          });
  //          this.messages = newMessages;
  //        })
  //    },
  //    openVideoRec: function() {
  //      this.showVideoRec = true;
  //      const options = {
  //        controls: true,
  //        fluid: true,
  //        bigPlayButton: true,
  //        controlBar: {
  //          volumePanel: true
  //        },
  //        plugins: {
  //          record: {
  //            audio: true,
  //            video: true,
  //            maxLength: 10,
  //            displayMilliseconds: true,
  //            debug: true,
  //            convertEngine: 'ts-ebml'
  //          }
  //        }
  //      };
  //      const player = videojs('myVideo', options, function() {
  //      });
  //      const self = this;
  //      player.on('finishRecord', function() {
  //        console.log('finished recording: ', player.recordedData);
  //        self.sortableMedias.push({
  //          src: URL.createObjectURL(player.recordedData),
  //          file: player.recordedData,
  //          type: 'video/mp4',
  //        });
  //      });
  //    },
  //    hideVideoRec: function() {
  //      this.showVideoRec = false;
  //    },
  //    onGetAudioRec: function(data) {
  //      const self = this;
  //      this.sortableMedias.push({
  //        src: URL.createObjectURL(data),
  //        file: data,
  //        type: 'audio/mp3',
  //      });
  //      setTimeout(() => {
  //        self.hideAudioRec();
  //      }, 1000);
  //    },
  //    hideAudioRec: function() {
  //      this.showAudioRec = false;
  //      this.audioRecDuration = 0;
  //      clearInterval(this.audioRecInterval);
  //      this.audioRecInterval = undefined;
  //    },
  //    toggleAudioRec: function() {
  //      const self = this;
  //      if (!this.audioRecInterval) {
  //        this.audioRecInterval = setInterval(function() {
  //          self.audioRecDuration += 1;
  //        }, 1000);
  //      } else {
  //        setTimeout(() => {
  //          self.hideAudioRec();
  //        }, 1000);
  //      }
  //    },
  //    showMediaPopup: function(media) {
  //      this.popupMedia = media;
  //      this.$refs['media-modal'].show();
  //    },
  //    closeMediaPopup: function() {
  //      this.popupMedia = undefined;
  //      this.$refs['media-modal'].hide();
  //    },
  //    onChangeScheduledMessageTime: function(event) {
  //      this.scheduledMessage.timeState = true;
  //      if (moment().format('YYYY-MM-DD') === this.$refs.schedule_date.value) {
  //        if (moment().format('HH:mm:ss') > event) {
  //          this.scheduledMessage.timeState = false;
  //        }
  //      }
  //      this.scheduledMessage = { ...this.scheduledMessage };
  //    },
  //    onCheckReturnKey: function(e) {
  //      if (e.ctrlKey && e.keyCode === 13) {
  //        this.sendMessage();
  //      }
  //    },
</script>

<style lang="scss" scoped>
.btn-link:hover {
  text-decoration: none;
}
.btn:focus, .btn.focus {
  box-shadow: none;
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
    "tooltips": {
      "gallery": "View Gallery of Media",
      "tip": "Send Tip"
    }
  }
}
</i18n>
