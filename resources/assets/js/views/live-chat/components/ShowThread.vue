<template>
  <div v-if="!isLoading">

    <section class="chatthread-header">
      <div class="d-flex align-items-center">
        <b-button to="/messages" variant="link" class="" @click="doSomething">
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
          v-for="(cm, idx) in chatmessages"
          :key="cm.id"
          class=""
        >
          <section v-if="isDateBreak(cm, idx)" class="msg-grouping-day-divider"><span>{{ moment(cm.created_at).format('MMM DD, YYYY') }}</span></section>
          <section class="crate" :class="cm.sender_id===session_user.id ? 'tag-session_user' : 'tag-other_user'">
            <article class="box">
              <div class="msg-content">{{ cm.mcontent }}</div>
              <div class="msg-timestamp">{{ moment(cm.created_at).format('h:mm A') }}</div>
            </article>
          </section>
        </b-list-group-item>
      </b-list-group>
    </section>


    <section class="conversation-footer mt-3 OFF-p-3">

      <div class="scheduled-message-head" v-if="isScheduled">
        <div>
          <fa-icon :icon="['fas', 'schedule']" class="fa-lg" fixed-width />
          <span> Scheduled for</span>
          <strong>{{ moment(deliverAtTimestamp * 1000).local().format('MMM DD, h:mm a') }}</strong>
        </div>
        <b-button variant="link" class="clickme_to-cancel" @click="clearScheduled">
          <fa-icon :icon="['fas', 'close']" class="clickable fa-lg" fixed-width />
        </b-button>
      </div>

      <b-form class="store-chatmessage" @submit.prevent="sendMessage($event)">
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
          <b-button variant="link" class="clickme_to-set_scheduled" :disabled="false" @click="openScheduleMessageModal('set-scheduled')">
            <fa-icon :icon="['far', 'calendar-alt']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button variant="link" class="clickme_to-set-price" :disabled="false" @click="doSomething('set-price')">
            <fa-icon :icon="['fas', 'dollar-sign']" class="clickable fa-lg" fixed-width />
          </b-button>
          <b-button type="submit" variant="primary" class="clickme_to-submit_message ml-auto" :disabled="false">SEND</b-button>
        </div>
      </b-form>

    </section>

    <b-modal id="schedule-message-modal" hide-header centered hide-footer ref="schedule-message-modal">
      <div class="block-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">SCHEDULED MESSAGES</h4>
        </div>
        <div class="content">
          <b-form-datepicker
            v-model="newMessageForm.deliver_at.date"
            :state="newMessageForm.deliver_at.date ? true : null"
            :min="new Date()"
          />
          <b-form-timepicker
            v-model="newMessageForm.deliver_at.time"
            :state="newMessageForm.deliver_at.time ? true : null"
          ></b-form-timepicker>
        </div>
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="clearScheduled">Cancel</button>
          <button class="link-btn" @click="setScheduled" >Apply</button>
        </div>
      </div>
    </b-modal>

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

    isLoading() {
      return !this.session_user || !this.id || !this.chatmessages
    },

    isScheduled() {
      return this.newMessageForm.deliver_at.date && this.newMessageForm.deliver_at.time
    },

    deliverAtTimestamp() {
      return this.isScheduled
        ? moment( `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}` ).utc().unix()
        : null
    },

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: null,
      deliver_at: { date: null, time: null },
      //deliverAtTimestamp: null,
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
    const channel = `chatthreads.${this.id}`
    this.$echo.private(channel).listen('.chatmessage.sent', e => {
      this.chatmessages.push(e.chatmessage)
    })
  },

  methods: {

    isDateBreak(cm, idx) {
      if (idx===0) {
        return true
      }
      const current = moment(this.chatmessages[idx].created_at);
      const last = moment(this.chatmessages[idx-1].created_at,);
      return !current.isSame(last, 'date')
    },

    async sendMessage(e) {
      const params = {
        mcontent: this.newMessageForm.mcontent,
      }
      let response
      if ( this.isScheduled ) {
        params.deliver_at_string = `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}`
        params.deliver_at = this.deliverAtTimestamp
        response = await axios.post( this.$apiRoute('chatthreads.scheduleMessage', this.id), params )
      } else {
        response = await axios.post( this.$apiRoute('chatthreads.sendMessage', this.id), params )
      }
      this.clearForm()
    },

    clearForm() {
      this.newMessageForm.mcontent = null
      this.clearScheduled()
    },

    setScheduled: function() {
      this.$bvModal.hide('schedule-message-modal')
      //this.$refs['schedule-message-modal'].hide();
    },

    clearScheduled: function() {
      this.newMessageForm.deliver_at.date = null
      this.newMessageForm.deliver_at.time = null
      //this.$refs['schedule-message-modal'].hide();
      this.$bvModal.hide('schedule-message-modal')
    },

    async getChatmessages(chatthreadID) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
        chatthread_id: chatthreadID,
      }
      const response = await axios.get( this.$apiRoute('chatmessages.index'), { params } )
      this.chatmessages = response.data.data.slice().reverse() // %NOTE: reverse order here for display!
      this.meta = response.meta
    },

    doSomething() {
      // stub placeholder for impl
    },

    openScheduleMessageModal: function() {
      this.$refs['schedule-message-modal'].show();
    },



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
  //    openVaultModal: function() {
  //      this.$refs['vault-modal'].show();
  //      this.isVaultLoading = true;
  //      this.axios.get('/vaults/all-files')
  //        .then(response => {
  //          this.vaultFiles = response.data.mediafiles;
  //          this.isVaultLoading = false;
  //        })
  //    },
  //    closeVaultModal: function() {
  //      this.filesFromVault = [];
  //      this.$refs['vault-modal'].hide();
  //    },
  //    showMediaPopup: function(media) {
  //      this.popupMedia = media;
  //      this.$refs['media-modal'].show();
  //    },
  //    closeMediaPopup: function() {
  //      this.popupMedia = undefined;
  //      this.$refs['media-modal'].hide();
  //    },
  //    addVaultFilestoMedias: function() {
  //      const self = this;
  //      this.filesFromVault.forEach(file => {
  //        self.sortableMedias.push({
  //          src: file.filepath,
  //          file: file.id,
  //          type: file.mimetype,
  //          mftype: 'vault',
  //        });
  //      });
  //      this.closeVaultModal();
  //    },
  //    selectVaultFiles: function(mediafile) {
  //      const idx = this.filesFromVault.findIndex(m => m.id === mediafile.id);
  //      if (idx < 0) {
  //        this.filesFromVault.push(mediafile);
  //      } else {
  //        this.filesFromVault.splice(idx, 1);
  //      }
  //    },
  //    filterVaultFiles: function(filterOption) {
  //      if (this.selectedVaultFilter === filterOption) {
  //        this.selectedVaultFilter = undefined;
  //        this.isVaultLoading = true;
  //        this.axios.get(`/vaults/all-files`)
  //          .then(response => {
  //            this.vaultFiles = response.data.mediafiles;
  //            this.isVaultLoading = false;
  //          })
  //      } else {
  //        this.selectedVaultFilter = filterOption;
  //        this.isVaultLoading = true;
  //        this.axios.get(`/vaults/all-files?query=${filterOption}`)
  //          .then(response => {
  //            this.vaultFiles = response.data.mediafiles;
  //            this.isVaultLoading = false;
  //          })
  //      }
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
  //
  //    // %TODO: new message activity (typing) to alert particpants that a new sender is typing...
  //    onInputNewMessage: function(e) {
  //      this.newMessageText = e.target.value;
  //      this.adjustTextareaSize();
  //      if (this.newMessageText) {
  //        this.hasNewMessage = true;
  //      } else {
  //        this.hasNewMessage = false;
  //      }
  //      let channel = Echo.join(`chat-typing`);
  //      setTimeout(() => {
  //        channel.whisper('typing', {
  //          typing: true,
  //          from: this.session_user.id,
  //          to: this.$route.params.id
  //        })
  //      }, 300);
  //    },
</script>

<style lang="scss" scoped>
body {
  .btn-link:hover {
    text-decoration: none;
  }
  .btn:focus, .btn.focus {
    box-shadow: none;
  }

  .list-group.tag-messages {

    overflow-y: scroll;

    .list-group-item {

       border: none;
       padding: 0.5rem 1.25rem;

      .crate {
        display: flex;
        max-width: 75%;

        .box {
          .msg-content {
            margin-left: auto;
            background: rgba(218,237,255,.53);
            border-radius: 5px;
            padding: 9px 12px;
            color: #1a1a1a;
          }
          .msg-timestamp {
            font-size: 11px;
            color: #8a96a3;
            text-align: right;
          }

        } // box
      } // crate

      .crate.tag-session_user {
         justify-content: flex-end;
         margin-left: auto;
         margin-right: 0;
      }

      .crate.tag-other_user {
         justify-content: flex-start;
         margin-left: 0;
         margin-right: auto;
      }

    }

    .msg-grouping-day-divider {
      font-size: 11px;
      line-height: 15px;
      text-align: center;
      color: #8a96a3;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 10px;
      span {
        padding: 0 10px;
      }
      &:after, &:before {
        content: '';
        display: block;
        flex: 1;
        height: 1px;
        background: rgba(138,150,163,.2);
      }
    }

  }

}

.conversation-footer {
  background-color: #fff;
  border-top: solid 1px rgba(138,150,163,.25);
}
button.clickme_to-submit_message {
  width: 9rem;
}
</style>

<style lang="scss">
body {
  form.store-chatmessage {
    textarea.form-control {
      border: none;
      //padding: 0;
    }
  }
}
</style>
