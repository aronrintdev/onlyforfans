<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg">
      </div>
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-body nopadding">
              <div class="top-bar">
                <div>
                  <router-link :to="`/messages`">
                    <button class="btn" type="button"> 
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                  </router-link>
                  <span class="top-bar-title">Scheduled Messages</span>
                </div>
              </div>
              <div class="gallery-content" v-if="!scheduled_messages.length">
                <div class="empty">Nothing was found</div>
              </div>
              <ul class="schedule-message-list" v-if="scheduled_messages.length">
                <li v-for="scheduled_message in scheduled_messages" :key="scheduled_message.id">
                  <div class="user-content" :class="`user-${scheduled_message.id}`">
                    <div class="user-logo text-logo" v-if="!scheduled_message.profile.avatar">
                      {{ getLogoFromName(scheduled_message.profile.name) }}
                      <span :class="`status-holder status-holder-${scheduled_message.profile.id}`"></span>
                    </div>
                    <div class="user-logo" v-if="scheduled_message.profile.avatar">
                      <img :src="scheduled_message.profile.avatar.filepath" alt="" />
                      <span :class="`status-holder status-holder-${scheduled_message.profile.id}`"></span>
                    </div>
                    <div class="user-details">
                      <div class="user-details-row">
                        <div>
                          <span class="username">{{ scheduled_message.profile.display_name ? scheduled_message.profile.display_name : scheduled_message.profile.name }}</span>
                        </div>
                        <div>
                          <!-- Date  -->
                          <span class="last-message-date">in {{ moment(scheduled_message.schedule_datetime * 1000).local().fromNow(true) }}</span>
                          <b-dropdown class="filter-dropdown sidebar-more-dropdown" right>
                            <template #button-content>
                              <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                            </template>
                            <b-dropdown-item v-on:click.stop.prevent="deleteScheduleMsg(scheduled_message)">
                              Delete scheduled message
                            </b-dropdown-item>
                            <b-dropdown-item v-on:click.stop.prevent="editScheduleMsg(scheduled_message)">
                              Edit scheduled message
                            </b-dropdown-item>
                          </b-dropdown>
                        </div>
                      </div>
                      <div class="user-details-row">
                        <div>
                          <span class="userId">@{{ scheduled_message.profile.username }}</span>
                        </div>
                        <div class="price" v-if="scheduled_message.tip_price">
                          <span class="">{{ scheduled_message.tip_price | niceCurrency }}</span>
                          <svg class="icon-locked" viewBox="0 0 24 24">
                            <path d="M17.5,8H17V6A5,5,0,0,0,7,6V8H6.5A2.5,2.5,0,0,0,4,10.5V18a4,4,0,0,0,4,4h8a4,4,0,0,0,4-4V10.5A2.5,2.5,0,0,0,17.5,8ZM9,6a3,3,0,0,1,6,0V8H9Zm9,12a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2V10.5a.5.5,0,0,1,.5-.5h11a.5.5,0,0,1,.5.5Zm-6-5a1,1,0,0,0-1,1v2a1,1,0,0,0,2,0V14A1,1,0,0,0,12,13Z"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="user-text-row">
                    <template v-for="msg in scheduled_message.messages">
                      <!-- Text -->
                      <div :key="msg.id" v-if="msg.mcontent">
                        <span class="last-message">{{ msg.mcontent }}</span>
                        <svg class="icon-price-free" viewBox="0 0 24 24">
                          <path d="M21 10.6l-1.67-1.69-1.42 1.42L19.6 12a1.4 1.4 0 010 2L14 19.6a1.45 1.45 0 01-2 0l-1.68-1.69-1.41 1.42L10.6 21a3.4 3.4 0 004.8 0l5.6-5.6a3.4 3.4 0 000-4.8zM7.7 6a1.7 1.7 0 101.7 1.7A1.7 1.7 0 007.7 6zM22 3a1 1 0 00-1-1 1 1 0 00-.71.29l-3.79 3.8-2.91-2.92A4.06 4.06 0 0010.76 2H5a3 3 0 00-3 3v5.76a4 4 0 001.17 2.83l2.92 2.91-3.8 3.79A1 1 0 002 21a1 1 0 001 1 1 1 0 00.71-.29l18-18A1 1 0 0022 3zM7.5 15.09l-2.91-2.92A2 2 0 014 10.76V5a1 1 0 011-1h5.76a2 2 0 011.41.59l2.92 2.91z"></path>
                        </svg>
                      </div>
                    </template>
                    <!-- videos / images Carousel -->
                    <MediaSlider
                      @click="renderFull"
                      :mediafiles="scheduled_message.mediafiles" 
                      :session_user="session_user"
                      v-if="scheduled_message.mediafiles.length > 0"
                      :use_mid="false" />
                    <template v-for="msg in scheduled_message.messages">
                      <!-- audio -->
                      <audio :key="msg.id" v-if="msg.mediafile && msg.mediafile.mimetype.indexOf('audio/') > -1" controls>
                        <source :src="msg.mediafile.filepath" type="audio/mpeg" />
                      </audio>
                      <audio :key="msg.id" v-if="msg.mediafile && msg.mediafile.mimetype === 'video/webm'" controls>
                        <source :src="msg.mediafile.filepath" type="video/webm" />
                      </audio>
                    </template>
                  </div>
                  <div class="divider"></div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <b-modal modal-class="unsend-message-modal" hide-header centered hide-footer ref="delete_confirm_modal" title="Delete Schedule Modal">
      <div class="block-modal">
        <h4>DELETE SCHEDULED MESSAGE</h4>
        <div class="content mb-3 mt-3">
          Are you sure?
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeDeleteModal">Cancel</button>
          <button class="link-btn" @click="confirmDelete">Yes, Delete</button>
        </div>
      </div>
    </b-modal>
    <b-modal modal-class="schedule-message-modal" hide-header centered hide-footer ref="edit_confirm_modal">
      <div class="block-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">SCHEDULED MESSAGES</h4>
        </div>
        <div class="content">
          <b-form-datepicker
            v-model="scheduledMessage.date"
            :value="scheduledMessage.date"
            class="mb-3 mt-1"
            ref="schedule_date"
            :state="scheduledMessage.date ? true : null"
            :min="new Date()"
          />
          <b-form-timepicker
            v-model="scheduledMessage.time"
            :state="scheduledMessage.timeState"
            class="mb-2"
            locale="en"
            @input="onChangeScheduledMessageTime"
          ></b-form-timepicker>
        </div>
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="closeEditModal">Cancel</button>
          <button
            class="link-btn"
            @click="confirmEdit"
            :disabled="!scheduledMessage.date || !scheduledMessage.time || !scheduledMessage.timeState"
          >Apply</button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
  /**
   * Scheduled Messages
   */
  import moment from 'moment';
  import _ from 'lodash';
  import Vuex from 'vuex';

  import MediaSlider from '@components/posts/MediaSlider';

  export default {
    //
    data: () => ({
      scheduled_messages: [],
      moment,
      deletingSchedule: undefined,
      editingSchedule: undefined,
      scheduledMessage: {}
    }),
    components: {
      MediaSlider,
    },
    mounted() {
      this.axios.get(`/chat-messages/scheduled`)
        .then((res) => {
          const scheduled_messages = res.data;
          scheduled_messages.forEach((scheduled_message) => {
            const messagesWithMedia = scheduled_message.messages.filter(msg => msg.mediafile && (msg.mediafile.mimetype.indexOf('video/') > -1 || msg.mediafile.mimetype.indexOf('image/') > -1));
            scheduled_message.mediafiles = messagesWithMedia.map(msg => msg.mediafile);
          });
          this.scheduled_messages = _.cloneDeep(scheduled_messages);
        });
    },
    computed: {
      ...Vuex.mapGetters(['session_user']),
    },
    methods: {
      editScheduleMsg: function(message) {
        this.editingSchedule = message;
        const utcDate = moment.utc(message.schedule_datetime * 1000).toDate();
        this.scheduledMessage = {
          date: moment(utcDate).local().format('YYYY-MM-DD'),
          time: moment(utcDate).local().format('HH:mm:ss'),
          timeState: true,
        };
        this.$refs.edit_confirm_modal.show();
      },
      deleteScheduleMsg: function(message) {
        this.deletingSchedule = message;
        this.$refs.delete_confirm_modal.show();
      },
      confirmDelete: function () {
        this.axios.delete(`/chat-messages/scheduled/${this.deletingSchedule.id}`)
          .then(() => {
            const messages = [...this.scheduled_messages];
            const idx = messages.findIndex(msg => msg.id === this.deletingSchedule.id);
            messages.splice(idx, 1);
            this.scheduled_messages = messages;
            this.closeDeleteModal();
          });
      },
      confirmEdit: function () {
        if (this.scheduledMessage.date && this.scheduledMessage.time) {
          const scheduledMessageDate = moment(`${this.scheduledMessage.date} ${this.scheduledMessage.time}`).utc().unix();
          const data = {
            schedule_datetime: scheduledMessageDate,
          };
          this.axios.patch(`/chat-messages/scheduled/${this.editingSchedule.id}`, data)
            .then(() => {
              const messages = [...this.scheduled_messages];
              const idx = messages.findIndex(msg => msg.id === this.editingSchedule.id);
              messages[idx].schedule_datetime = data.schedule_datetime;
              this.scheduled_messages = messages;
              this.closeEditModal();
            });
        }
      },
      closeDeleteModal: function() {
        this.$refs.delete_confirm_modal.hide();
        this.deletingSchedule = undefined;
      },
      closeEditModal: function() {
        this.$refs.edit_confirm_modal.hide();
        this.editingSchedule = undefined;
        this.scheduledMessage = {};
      },
      getLogoFromName: function (username) {
        const names = username.split(' ');
        if (names.length === 1) {
          return username.slice(0, 2);
        }
        return names[0].slice(0, 1) + names[1].slice(0, 1);
      },
      renderFull: function() {

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
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/scheduled.scss";
</style>
