<template>
  <div v-if="!isLoading">

    <section class="chatthread-header">
      <div class="d-flex align-items-center">
        <b-button to="/messages" variant="link" class="" @click="doSomething">
          <fa-icon :icon="['fas', 'arrow-left']" class="fa-lg" />
        </b-button>
        <p class="m-0"><strong>{{ participant.username }}</strong></p>
      </div>
      <div class="d-flex align-items-center">
        <p class="my-0 mx-2">Last Seen</p>
        <div>|</div>
        <b-button variant="link" class="" @click="doSomething">
          <fa-icon :icon="['far', 'star']" class="fa-lg" />
        </b-button>
        <div>|</div>
        <b-button variant="link" class="" @click="doSomething">
          <fa-icon :icon="['far', 'bell']" class="fa-lg" />
        </b-button>
        <div>|</div>
        <b-button variant="link" class="" @click="doSomething">
          <fa-icon :icon="['far', 'image']" class="fa-lg" />
        </b-button>
        <div>|</div>
        <b-button variant="link" class="" @click="doSomething">
          <fa-icon :icon="['fas', 'search']" class="fa-lg" />
        </b-button>
      </div>
    </section>

    <hr />

    <section>

      <b-list-group class="tag-messages">
        <b-list-group-item
          v-for="(cm, idx) in chatmessages.slice().reverse()"
          :key="cm.id"
          class=""
        >
          <div>{{ cm.mcontent }}</div>
        </b-list-group-item>
      </b-list-group>
    </section>


    <section class="conversation-footer p-3">

      <b-form @submit.prevent="sendMessage($event)">
        <div>
          <b-form-group id="newMessage-group-1" class="">
            <b-form-textarea
              v-model="newMessageForm.mcontent"
              placeholder="Type a message..."
              rows="2"
              max-rows="6"
              spellcheck="false"
            ></b-form-textarea>
          </b-form-group>
        </div>
        <div class="form-ctrl d-flex">
          <b-button variant="link" class="clickme_to-attach_files" @click="doSomething">
            <fa-icon :icon="['far', 'file-alt']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-record_video" @click="doSomething('record-video')">
            <fa-icon :icon="['fas', 'video']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-record_audio" @click="doSomething('record-audio')">
            <fa-icon :icon="['fas', 'microphone']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-select_vault_file" @click="doSomething('select-vault-file')">
            <fa-icon :icon="['fas', 'archive']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-set_scheduled" :disabled="false" @click="doSomething('set-scheduled')">
            <fa-icon :icon="['far', 'calendar-alt']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-set-price" :disabled="false" @click="doSomething('set-price')">
            <fa-icon :icon="['fas', 'dollar-sign']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button type="submit" variant="primary" class="clickme_to-submit_message ml-auto" :disabled="false">SEND</b-button>
        </div>
      </b-form>


    </section>

  </div>
</template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'

export default {
  //name: 'LivechatDashboard',

  props: {
    session_user: null,
    participant: null,
    id: null, // the chatthread PKID
  },

  computed: {
    //...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.id || !this.chatmessages
    },

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: null,
    },

    chatmessages: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

  }), // data

  created() { 
  },

  mounted() { 
    this.getChatmessages(this.id)

    //const channel = `private-chatthreads.${this.id}`
    const channel = `chatthreads.${this.id}`
    //const eventName = `MessageSentEvent`
    console.log(`live-chat/components/ShowThread::mounted`, {
      channel: channel,
      //eventName: eventName,
    })
    this.$echo.private(channel).listen('.chatmessage.sent', e => {
      console.log(`live-chat/components/ShowThread::echo.listen`, {
        channel: channel,
        msg: e.chattmessage
      })
    })
      /*
    Echo.private(channel).listen('MessageSentEvent', e => {
      console.log(`live-chat/components/ShowThread::echo.listen`, {
        channel: channel,
        //eventName: eventName,
        msg: e.chattmessage
      })
    })
       */
      /*
    Echo.private(channel).listen('MessageSentEvent', e => {
            console.log('echo', e.chattmessage);
    })
       */
  },

  methods: {

    //...Vuex.mapActions([
    //'getMe',
    //]),

    async sendMessage(e) {
      console.log('live-chat.components.ShowThread::sendMessage().A')
      const params = {
        mcontent: this.newMessageForm.mcontent,
      }
      const response = await axios.post( this.$apiRoute('chatthreads.sendMessage', this.id), params )
      console.log('live-chat.components.ShowThread::sendMessage().B')
      this.newMessageForm.mcontent = null
    },

    async getChatmessages(chatthreadID) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
        chatthread_id: chatthreadID,
      }
      const response = await axios.get( this.$apiRoute('chatmessages.index'), { params } )
      this.chatmessages = response.data.data
      this.meta = response.meta
    },

    doSomething() {
      // stub placeholder for impl
    },

    setFollowForFree: function(userId) {
      this.axios.patch(`/users/${userId}/settings`, {
        is_follow_for_free: true,
      }).then(() => {
        this.selectedUser = {
          ...this.selectedUser,
          profile: {
            ...this.selectedUser.profile,
            is_follow_for_free: true,
          }
        };
      });
    },
    openMessagePriceModal: function() {
      this.tempMessagePrice = undefined;
      this.$refs['message-price-modal'].show();
    },
    closeMessagePriceModal: function() {
      this.tempMessagePrice = undefined;
      this.$refs['message-price-modal'].hide();
    },
    saveMessagePrice: function() {
      this.messagePrice = this.tempMessagePrice;
      this.$refs['message-price-modal'].hide();
      console.log('messagePrice:', this.messagePrice);
    },
    onMessagePriceChange: function(val) {
      if (val < 5) {
        this.tempMessagePrice = 5;
      } else {
        this.tempMessagePrice = val;
      }
    },
    clearMessagePrice: function() {
      this.messagePrice = undefined;
    },
    openUnsendMessageModal: function(messageId) {
      this.$refs['unsend-message-modal'].show();
      this.unsendTipMessageId = messageId;
    },
    closeUnsendMessageModal: function() {
      this.unsendTipMessageId = undefined;
      this.$refs['unsend-message-modal'].hide();
    },
    unsendTipMessage: function() {
      const self = this;
      if (this.unsendTipMessageId) {
        this.axios.delete(`/chat-messages/${this.$route.params.id}/threads/${this.unsendTipMessageId}`)
          .then(() => {
            const idx = self.originMessages.findIndex(message => message.id === self.unsendTipMessageId);
            self.originMessages.splice(idx, 1);
            self.originMessages = _.cloneDeep(self.originMessages);
            self.groupMessages();
            self.closeUnsendMessageModal();
          });
      }
    },
    openMessagePriceConfirmModal: function(value) {
      this.confirm_message_price = value;
      this.$refs['confirm-message-price-modal'].show();
    },
    closeMessagePriceConfirmModal: function() {
      this.$refs['confirm-message-price-modal'].hide();
    },
    onCheckReturnKey: function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        this.sendMessage();
      }
    },
    clearMessages: function (receiver) {
      this.axios.delete(`/chat-messages/${receiver.id}`)
        .then(() => {
          const idx = this.users.findIndex(user => user.profile.id === receiver.id);
          this.users.splice(idx, 1);
          this.$router.push('/messages');
        })
    },
    setLikeMessage: function(message) {
      this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/like`)
        .then(() => {
          const newMessages = [...this.messages];
          newMessages.forEach(thread => {
            const idx = thread.messages.findIndex(m => m.id === message.id);
            if (idx > -1) {
              thread.messages[idx].is_like = 1;
            }
          });
          this.messages = newMessages;
        })
    },
    setUnlikeMessage: function(message) {
      this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/unlike`)
        .then(() => {
          const newMessages = [...this.messages];
          newMessages.forEach(thread => {
            const idx = thread.messages.findIndex(m => m.id === message.id);
            if (idx > -1) {
              thread.messages[idx].is_like = 0;
            }
          });
          this.messages = newMessages;
        })
    },
    openVideoRec: function() {
      this.showVideoRec = true;
      const options = {
        controls: true,
        fluid: true,
        bigPlayButton: true,
        controlBar: {
          volumePanel: true
        },
        plugins: {
          record: {
            audio: true,
            video: true,
            maxLength: 10,
            displayMilliseconds: true,
            debug: true,
            convertEngine: 'ts-ebml'
          }
        }
      };
      const player = videojs('myVideo', options, function() {
      });
      const self = this;
      player.on('finishRecord', function() {
        console.log('finished recording: ', player.recordedData);
        self.sortableMedias.push({
          src: URL.createObjectURL(player.recordedData),
          file: player.recordedData,
          type: 'video/mp4',
        });
      });
    },
    hideVideoRec: function() {
      this.showVideoRec = false;
    },
    onGetAudioRec: function(data) {
      const self = this;
      this.sortableMedias.push({
        src: URL.createObjectURL(data),
        file: data,
        type: 'audio/mp3',
      });
      setTimeout(() => {
        self.hideAudioRec();
      }, 1000);
    },
    hideAudioRec: function() {
      this.showAudioRec = false;
      this.audioRecDuration = 0;
      clearInterval(this.audioRecInterval);
      this.audioRecInterval = undefined;
    },
    toggleAudioRec: function() {
      const self = this;
      if (!this.audioRecInterval) {
        this.audioRecInterval = setInterval(function() {
          self.audioRecDuration += 1;
        }, 1000);
      } else {
        setTimeout(() => {
          self.hideAudioRec();
        }, 1000);
      }
    },
    openVaultModal: function() {
      this.$refs['vault-modal'].show();
      this.isVaultLoading = true;
      this.axios.get('/vaults/all-files')
        .then(response => {
          this.vaultFiles = response.data.mediafiles;
          this.isVaultLoading = false;
        })
    },
    closeVaultModal: function() {
      this.filesFromVault = [];
      this.$refs['vault-modal'].hide();
    },
    showMediaPopup: function(media) {
      this.popupMedia = media;
      this.$refs['media-modal'].show();
    },
    closeMediaPopup: function() {
      this.popupMedia = undefined;
      this.$refs['media-modal'].hide();
    },
    addVaultFilestoMedias: function() {
      const self = this;
      this.filesFromVault.forEach(file => {
        self.sortableMedias.push({
          src: file.filepath,
          file: file.id,
          type: file.mimetype,
          mftype: 'vault',
        });
      });
      this.closeVaultModal();
    },
    selectVaultFiles: function(mediafile) {
      const idx = this.filesFromVault.findIndex(m => m.id === mediafile.id);
      if (idx < 0) {
        this.filesFromVault.push(mediafile);
      } else {
        this.filesFromVault.splice(idx, 1);
      }
    },
    filterVaultFiles: function(filterOption) {
      if (this.selectedVaultFilter === filterOption) {
        this.selectedVaultFilter = undefined;
        this.isVaultLoading = true;
        this.axios.get(`/vaults/all-files`)
          .then(response => {
            this.vaultFiles = response.data.mediafiles;
            this.isVaultLoading = false;
          })
      } else {
        this.selectedVaultFilter = filterOption;
        this.isVaultLoading = true;
        this.axios.get(`/vaults/all-files?query=${filterOption}`)
          .then(response => {
            this.vaultFiles = response.data.mediafiles;
            this.isVaultLoading = false;
          })
      }
    },
    openScheduleMessageModal: function() {
      this.$refs['schedule-message-modal'].show();
    },
    applySchedule: function() {
      this.scheduledMessageDate = moment(`${this.scheduledMessage.date} ${this.scheduledMessage.time}`).utc().unix();
      this.$refs['schedule-message-modal'].hide();
      this.scheduledMessage = {};
    },
    clearSchedule: function() {
      this.scheduledMessageDate = null;
      this.scheduledMessage = {};
      this.$refs['schedule-message-modal'].hide();
    },
    onChangeScheduledMessageTime: function(event) {
      this.scheduledMessage.timeState = true;
      if (moment().format('YYYY-MM-DD') === this.$refs.schedule_date.value) {
        if (moment().format('HH:mm:ss') > event) {
          this.scheduledMessage.timeState = false;
        }
      }
      this.scheduledMessage = { ...this.scheduledMessage };
    },

    /*
    onCheckReturnKey: function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        this.sendMessage();
      }
    },

    // %TODO: new message activity (typing) to alert particpants that a new sender is typing...
    onInputNewMessage: function(e) {
      this.newMessageText = e.target.value;
      this.adjustTextareaSize();
      if (this.newMessageText) {
        this.hasNewMessage = true;
      } else {
        this.hasNewMessage = false;
      }
      let channel = Echo.join(`chat-typing`);
      setTimeout(() => {
        channel.whisper('typing', {
          typing: true,
          from: this.session_user.id,
          to: this.$route.params.id
        })
      }, 300);
    },
     */

  }, // methods

  watch: {

    id (newValue, oldValue) {
      if ( newValue && (newValue!==oldValue) ) {
        this.getChatmessages(newValue)
      }
    },

  }, // watch

  components: { },

}
</script>

<style lang="scss" scoped>
body {
  .btn-link:hover {
    text-decoration: none;
  }
  .btn:focus, .btn.focus {
    box-shadow: none;
  }
}

.conversation-footer {
  background-color: #fff;
}
textarea {
  border: none;
}
button.clickme_to-submit_message {
  width: 9rem;
}
</style>

<style lang="scss">
</style>
