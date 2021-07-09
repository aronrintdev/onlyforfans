<template>
  <section v-if="!isLoading" class="conversation-footer d-flex flex-column">

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

    <!-- Photo Store display -->
    <div class="d-block w-100" v-if="selectedMediafiles.length > 0">
      <div class="d-flex">
        <b-btn variant="link" size="sm" class="ml-auto" @click="onClearFiles">
          {{ $t('clearFiles') }}
        </b-btn>
      </div>
      <UploadMediaPreview
        :mediafiles="selectedMediafiles"
        @change="changeMediafiles"
        @openFileUpload="openDropzone"
      />
    </div>

    <b-form class="store-chatmessage mt-auto" @submit.prevent="sendMessage($event)">
      <div>
        <b-form-group id="newMessage-group-1" class="">
          <b-form-textarea
            v-model="newMessageForm.mcontent"
            placeholder="Type a message..."
            :rows="mobile ? 2 : 3"
            max-rows="6"
            spellcheck="false"
          ></b-form-textarea>
        </b-form-group>
      </div>
      <div class="form-ctrl d-flex">
        <b-btn
          variant="link"
          class="clickme_to-attach_files"
          v-b-tooltip.hover
          title="Attach Files"
          @click="doSomething"
        >
          <fa-icon :icon="['far', 'file-alt']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn
          variant="link"
          class="clickme_to-record_video"
          v-b-tooltip.hover
          title="Record Video"
          @click="doSomething('record-video')"
        >
          <fa-icon :icon="['fas', 'video']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn
          variant="link"
          class="clickme_to-record_audio"
          v-b-tooltip.hover
          title="Record Audio"
          @click="doSomething('record-audio')"
        >
          <fa-icon :icon="['fas', 'microphone']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn
          variant="link"
          class="clickme_to-select_vault_file"
          v-b-tooltip.hover
          title="Attach Files From Your Vault"
          @click="toggleVaultSelect()"
        >
          <fa-icon :icon="['fas', 'archive']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn
          variant="link"
          class="clickme_to-set_scheduled"
          v-b-tooltip.hover
          title="Schedule Post To Be Sent At"
          :disabled="false"
          @click="openScheduleMessageModal('set-scheduled')"
        >
          <fa-icon :icon="['far', 'calendar-alt']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn
          variant="link"
          class="clickme_to-set-price"
          v-b-tooltip.hover
          title="Set Message Unlock Price"
          :disabled="false"
          @click="doSomething('set-price')"
        >
          <fa-icon :icon="['fas', 'dollar-sign']" class="clickable fa-lg" fixed-width />
        </b-btn>
        <b-btn type="submit" variant="primary" class="clickme_to-submit_message ml-auto" :disabled="false">SEND</b-btn>
      </div>
    </b-form>

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


  </section>
</template>

<script>
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'
import UploadMediaPreview from '@components/posts/UploadMediaPreview'

export default {
  name: 'MessageForm',

  components: {
    UploadMediaPreview,
  },

  props: {
    session_user: null,
    chatthread_id: null,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapState('messaging', [
      'selectedMediafiles'
    ]),

    isLoading() {
      return !this.session_user
    },

    isScheduled() {
      return this.newMessageForm.deliver_at.date && this.newMessageForm.deliver_at.time
    },

    deliverAtTimestamp() {
      return this.isScheduled
        ? moment( `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}` ).utc().unix()
        : null
    },

    channelName() {
      return `chatthreads.${this.chatthread_id}`
    }

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: null,
      deliver_at: { date: null, time: null },
    },

  }), // data

  created() {
    this.isTyping = _.throttle(this._isTyping, 1000)
  },

  mounted() { },

  methods: {
    ...Vuex.mapMutations('messaging', [
      'UPDATE_SELECTED_MEDIAFILES',
      'CLEAR_SELECTED_MEDIAFILES',
    ]),
    changeMediafiles(data) {
      this.UPDATE_SELECTED_MEDIAFILES([...data])
    },

    openDropzone() {
      // TODO: ADD dropzone file Uploading
    },

    onClearFiles() {
      this.CLEAR_SELECTED_MEDIAFILES()
    },

    async sendMessage(e) {
      let response
      const params = {
        mcontent: this.newMessageForm.mcontent,
      }

      if ( this.chatthread_id === 'new' ) {

        // %NOTE - Creating a new thread, delegate to parent template (CreateThreadForm), as
        //   that's where the selectedContact data resides
        params.is_scheduled = this.isScheduled
        if ( this.isScheduled ) {
          //params.deliver_at_string = `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}`
          params.deliver_at = this.deliverAtTimestamp
        }
        this.$emit('create-chatthread', params)
        this.clearForm() // %FIXME: how to confirm success before clearing form (?)

      } else if ( this.isScheduled ) {

        // 'send' a pre-scheduled message (on an existing thread)
        //params.deliver_at_string = `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}`
        params.deliver_at = this.deliverAtTimestamp
        response = await axios.post( this.$apiRoute('chatthreads.scheduleMessage', this.chatthread_id), params )
        this.clearForm()

      } else {

        // send an immediate message (on an existing thread)
        const message = {
          chatthread_id: this.chatthread_id,
          mcontent: this.newMessageForm.mcontent,
          sender_id: this.session_user.id,
          is_delivered: true,
          created_at: this.moment().toISOString(),
          updated_at: this.moment().toISOString(),
        }
        this.$log.debug('messageForm sendMessage', { message })
        this.$echo.join(this.channelName).whisper('sendMessage', { message })
        this.$emit('sendMessage', message)

        response = await axios.post( this.$apiRoute('chatthreads.sendMessage', this.chatthread_id), params )
        this.clearForm()
      }

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

    doSomething() {
      // stub placeholder for impl
    },

    openScheduleMessageModal: function() {
      this.$refs['schedule-message-modal'].show();
    },

    toggleVaultSelect() {
      this.$emit('toggleVaultSelect')
    },

    _isTyping() {
      this.$echo.join(this.channelName)
        .whisper('typing', {
          name: this.session_user.name || this.session_user.username
        })
    },

  }, // methods

  watch: {
    'newMessageForm.mcontent': function(value) {
      if (
        this.newMessageForm.deliver_at.date === undefined || this.newMessageForm.deliver_at.date === null
        && this.newMessageForm.time === undefined || this.newMessageForm.time === null
      ) {
        this.isTyping()
      }
    },

  }, // watch



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
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "clearFiles": "Clear Images"
  }
}
</i18n>
