<template>
  <section v-if="!isLoading" class="conversation-footer mt-3 d-flex flex-column">

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
import moment from 'moment'

export default {

  props: {
    session_user: null,
    chatthread_id: null,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),

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

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: null,
      deliver_at: { date: null, time: null },
    },

  }), // data

  created() { },

  mounted() { },

  methods: {

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

  }, // methods

  watch: { }, // watch

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
