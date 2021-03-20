<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg">

      </div>
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-body nopadding">
              <div class="row message-box">
                <div class="col-md-4 col-sm-4 col-xs-4 message-col-4">
                  <div class="top-bar" v-if="!userSearchVisible">
                    <div>
                      <router-link to="/">
                        <button class="btn" type="button">
                          <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        </button>
                      </router-link>
                      <span class="top-bar-title">Messages</span>
                    </div>
                    <div class="top-bar-action-btns">
                      <button class="btn" type="button" @click="changeSearchbarVisible">
                        <i class="fa fa-search" aria-hidden="true"></i>
                      </button>
                      <router-link to="/messages/new">
                        <button class="btn" type="button">
                          <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                      </router-link>
                    </div>
                  </div>
                  <div class="top-bar user-search-bar" v-if="userSearchVisible">
                    <button class="btn" type="button" @click="changeSearchbarVisible">
                      <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                    <b-form-input :value="userSearchText" @input="onUserSearch" placeholder="Search"></b-form-input>
                    <button class="btn" type="button" :disabled="!userSearchText">
                      <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                  </div>
                  <div class="options-bar">
                    <span class="selected-option">{{selectedOption}}</span>
                    <b-dropdown id="user-filter-dropdown" right>
                      <template #button-content>
                        <svg class="sort-icon has-tooltip" aria-hidden="true" data-original-title="null">
                          <use xlink:href="#icon-sort" href="#icon-sort">
                            <svg id="icon-sort" viewBox="0 0 24 24">
                              <path
                                d="M4 19h4a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1zM3 6a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1zm1 7h10a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1z">
                              </path>
                            </svg>
                          </use>
                        </svg>
                      </template>
                      <b-dropdown-item @click="onOptionChanged('recent')">
                        <b-form-radio v-model="optionValue" name="some-radios" value="recent">Recent</b-form-radio>
                      </b-dropdown-item>
                      <b-dropdown-item @click="onOptionChanged('unread_first')">
                        <b-form-radio v-model="optionValue" name="some-radios" value="unread_first">Unread First
                        </b-form-radio>
                      </b-dropdown-item>
                      <b-dropdown-item @click="onOptionChanged('oldest_unread_first')">
                        <b-form-radio v-model="optionValue" name="some-radios" value="oldest_unread_first">Oldest unread
                          first</b-form-radio>
                      </b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item disabled>Mark all as read</b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item>Select</b-dropdown-item>
                    </b-dropdown>
                  </div>
                  <div class="text-center" v-if="loading">
                    <b-spinner variant="secondary" label="Loading..." small></b-spinner>
                  </div>
                  <div class="no-users" v-if="!users.length">Nothing was found</div>
                  <ul class="user-list" v-if="users.length">
                    <li v-for="user in users" :key="user.profile.id"
                      :class="selectedUser && selectedUser.profile.id === user.profile.id ? 'selected' : ''"
                    >
                      <router-link :to="`/messages/${user.profile.id}`">
                        <div class="user-content">
                          <div class="user-logo text-logo" v-if="!user.profile.avatar">
                            {{ getLogoFromName(user.profile.name) }}
                            <span :class="`status-holder status-holder-${user.profile.id}`"></span>
                          </div>
                          <div class="user-logo" v-if="user.profile.avatar">
                            <img :src="user.profile.avatar.filepath" alt="" />
                            <span :class="`status-holder status-holder-${user.profile.id}`"></span>
                          </div>
                          <div class="user-details">
                            <div class="user-details-row">
                              <div>
                                <span class="username">{{ user.profile.name }}</span>
                                <span class="user-id">{{ `@${user.profile.username}` }}</span>
                              </div>
                              <!-- Close Button -->
                              <button class="close-btn btn" type="button" @click="clearMessages(user.profile)">
                                <i class="fa fa-times" aria-hidden="true"></i>
                              </button>
                            </div>
                            <div class="user-details-row">
                              <span class="last-message">{{ user.last_message.message }}</span>
                              <!-- Date  -->
                              <span class="last-message-date">{{ moment(user.last_message.created_at).format('MMM DD, YYYY') }}</span>
                            </div>
                          </div>
                        </div>
                        <div class="divider"></div>
                      </router-link>
                    </li>
                  </ul>
                </div>
                 <div v-if="selectedUser" class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div :class="messageSearchVisible ? 'conversation-header no-border' : 'conversation-header'">
                    <button class="back-btn btn" type="button" @click="clearSelectedUser">
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                    <div class="content">
                      <div class="user-name">
                        <span>{{ selectedUser.profile.name }}</span>
                      </div>
                      <div class="details" v-if="!messageSearchVisible">
                        <div class="online-status" v-if="!selectedUser.profile.user.is_online && selectedUser.profile.user.last_logged">Last seen {{ moment(selectedUser.profile.user.last_logged).format('MMM DD, YYYY') }}
                        </div>
                        <div class="online-status" v-if="!selectedUser.profile.user.is_online && !selectedUser.profile.user.last_logged">Last seen {{ moment(selectedUser.messages[0].created_at).format('MMM DD, YYYY') }}
                        </div>
                        <div class="online-status" v-if="selectedUser.profile.user.is_online">
                          <i class="fa fa-circle" aria-hidden="true"></i>Available now
                        </div>
                        <div class="v-divider"></div>
                        <button class="star-btn btn" type="button" @click="addToFavourites()">
                          <font-awesome-icon :icon="['far', 'star']" />
                        </button>
                        <div class="v-divider"></div>
                        <button class="notification-btn btn" type="button" @click="muteNotification()">
                          <font-awesome-icon :icon="['far', 'bell']" />
                        </button>
                        <div class="v-divider"></div>
                        <button class="gallery-btn btn" type="button">
                          <font-awesome-icon :icon="['far', 'image']" />&nbsp;&nbsp;Gallery

                        </button>
                        <div class="v-divider"></div>
                        <button class="search-btn btn" type="button" @click="changeMessageSearchVisible">
                          <font-awesome-icon :icon="['fas', 'search']" />&nbsp;&nbsp;Find
                        </button>
                      </div>
                    </div>

                    <b-dropdown id="more-dropdown" right>
                      <template #button-content>
                        <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                      </template>
                      <b-dropdown-item>
                        Copy link to profile
                      </b-dropdown-item>
                      <b-dropdown-item>
                        Add to / remove from lists
                      </b-dropdown-item>
                      <b-dropdown-item>
                        Give user a discount
                      </b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item>Hide chat</b-dropdown-item>
                      <b-dropdown-item>Mute notifications</b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item>Restrict @{{ selectedUser.profile.username }}</b-dropdown-item>
                      <b-dropdown-item>Block @{{ selectedUser.profile.username }}</b-dropdown-item>
                      <b-dropdown-item>Report @{{ selectedUser.profile.username }}</b-dropdown-item>
                    </b-dropdown>
                  </div>
                  <div class="details message-search" v-if="messageSearchVisible">
                    <div class="top-bar user-search-bar">
                      <button class="btn" type="button" @click="changeMessageSearchVisible">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </button>
                      <b-form-input v-model="userSearchText" placeholder="Find in chat"></b-form-input>
                      <button class="btn" type="button">
                        <i class="fa fa-search" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                  <div class="conversation-list">
                    <div class="empty-messages" v-if="messages.length === 0">
                      Type a message below to start a conversation with {{ selectedUser.name }}
                    </div>
                    <div class="messages" v-if="messages.length > 0">
                      <div class="text-center mb-2" v-if="loadingData"><b-spinner variant="secondary" label="Loading..." small></b-spinner></div>
                      <div class="message-group" :key="messageGroup.date"  v-for="messageGroup in messages">
                        <div class="message-group-time"><span>{{ moment.unix(messageGroup.date).format('MMM DD, YYYY') }}</span></div>
                        <template v-for="message in messageGroup.messages">
                          <div class="message" :key="message.id">
                            <div class="received" v-if="currentUser && currentUser.id !== message.user_id">
                              <div class="user-logo text-logo" v-if="selectedUser && !selectedUser.profile.avatar">
                                {{ getLogoFromName(selectedUser.name) }}
                              </div>
                              <div class="user-logo" v-if="selectedUser && selectedUser.profile.avatar">
                                <img :src="selectedUser.profile.avatar.filepath" alt="" />
                              </div>
                              <div class="content">
                                <div class="text">{{ message.message }}</div>
                                <div class="time">{{ moment(message.created_at).format('hh:mm A') }}</div>
                              </div>
                            </div>
                            <div class="sent" v-if="currentUser && currentUser.id === message.user_id">
                              <div class="text">{{ message.message }}</div>
                              <div class="time">{{ moment(message.created_at).format('hh:mm A') }}</div>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                    <div class="typing dot-pulse" style="display: none">...</div>
                  </div>
                  <div class="conversation-footer">
                    <textarea placeholder="Type a message" name="text" rows="1" maxlength="10000"
                      spellcheck="false" :value="newMessageText" @input="onInputNewMessage"></textarea>
                    <div class="action-btns">
                      <div>
                        <!-- image -->
                        <button class="btn action-btn" type="button">
                          <svg id="icon-media" viewBox="0 0 24 24"> <path d="M18,3H6A3,3,0,0,0,3,6V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V6A3,3,0,0,0,18,3Zm1,15a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H18a1,1,0,0,1,1,1ZM9,10.5A1.5,1.5,0,1,0,7.5,9,1.5,1.5,0,0,0,9,10.5ZM14,13l-3,3L9,14,6.85,16.15a.47.47,0,0,0-.14.35.5.5,0,0,0,.5.5h9.58a.5.5,0,0,0,.5-.5.47.47,0,0,0-.14-.35Z" fill="#8a96a3"></path> </svg>
                        </button>
                        <!-- video -->
                        <button class="btn action-btn" type="button">
                          <svg id="icon-video" viewBox="0 0 24 24">
                            <path
                              d="M21.79 6a1.21 1.21 0 0 0-.86.35L19 8.25V7a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-1.25l1.93 1.93a1.22 1.22 0 0 0 2.07-.86V7.18A1.21 1.21 0 0 0 21.79 6zM17 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1zm4-2.08l-1.34-1.34a2.25 2.25 0 0 1 0-3.16L21 9.08z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- microphone -->
                        <button class="btn action-btn" type="button">
                          <svg id="icon-voice" viewBox="0 0 24 24">
                            <path
                              d="M12 15a4 4 0 0 0 4-4V6a4 4 0 0 0-8 0v5a4 4 0 0 0 4 4zm-2-9a2 2 0 0 1 4 0v5a2 2 0 0 1-4 0zm9 4a1 1 0 0 0-1 1 6 6 0 0 1-12 0 1 1 0 0 0-2 0 8 8 0 0 0 7 7.93V21a1 1 0 0 0 2 0v-2.07A8 8 0 0 0 20 11a1 1 0 0 0-1-1z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- Medis from vault -->
                        <button class="btn action-btn" type="button">
                          <svg id="icon-vault" viewBox="0 0 24 24">
                            <path
                              d="M20.33,5.69h0l-.9-1.35A3,3,0,0,0,16.93,3H7.07a3,3,0,0,0-2.5,1.34l-.9,1.35A4,4,0,0,0,3,7.91V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.91A4,4,0,0,0,20.33,5.69ZM6.24,5.45A1,1,0,0,1,7.07,5h9.86a1,1,0,0,1,.83.45l.37.55H5.87ZM19,18a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V8H19ZM9.5,12.75A1.25,1.25,0,1,0,8.25,11.5,1.25,1.25,0,0,0,9.5,12.75ZM7.93,17h8.14a.42.42,0,0,0,.3-.73L13.7,13.6l-2.55,2.55-1.7-1.7L7.63,16.27a.42.42,0,0,0,.3.73Z"
                              fill="#8a96a3"></path>
                          </svg></button>
                        <!-- message price -->
                        <button class="btn action-btn" type="button">
                          <svg id="icon-price" viewBox="0 0 24 24">
                            <path
                              d="M22 13a3.38 3.38 0 0 0-1-2.4l-7.41-7.43A4.06 4.06 0 0 0 10.76 2H5a3 3 0 0 0-3 3v5.76a4 4 0 0 0 1.17 2.83L10.6 21a3.4 3.4 0 0 0 4.8 0l5.6-5.6a3.38 3.38 0 0 0 1-2.4zm-2.4 1L14 19.6a1.45 1.45 0 0 1-2 0l-7.41-7.43A2 2 0 0 1 4 10.76V5a1 1 0 0 1 1-1h5.76a2 2 0 0 1 1.41.59L19.6 12a1.4 1.4 0 0 1 0 2zM7.7 6a1.7 1.7 0 1 0 1.7 1.7A1.7 1.7 0 0 0 7.7 6zm6.16 6.28c-1-.22-1.85-.3-1.85-.78s.43-.51 1.06-.51a1.2 1.2 0 0 1 .92.43.48.48 0 0 0 .35.16h1.35a.23.23 0 0 0 .21-.22c0-.71-.86-1.55-2-1.84v-.75a.27.27 0 0 0-.26-.27h-1.27a.27.27 0 0 0-.26.27v.74a2.31 2.31 0 0 0-2 2c0 2.81 4.07 1.85 4.07 2.89 0 .48-.47.53-1.27.53a1.3 1.3 0 0 1-1-.52.66.66 0 0 0-.4-.17h-1.28a.23.23 0 0 0-.2.22c0 1 1 1.72 2.08 2v.74a.27.27 0 0 0 .26.27h1.25a.26.26 0 0 0 .26-.27v-.71A2.18 2.18 0 0 0 16 14.43c0-1.2-.86-1.88-2.14-2.15z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                      </div>
                      <button class="send-btn btn" :disabled="!hasNewMessage" type="button" @click="sendMessage">
                        Send
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';
  import _ from 'lodash';
  /**
   * Messages Dashboard View
   */
  export default {
    //
    data: () => ({
      userSearchText: undefined,
      userSearchVisible: false,
      optionValue: 'unread_first',
      selectedUser: undefined,
      users: [],
      loading: true,
      moment: moment,
      messageSearchVisible: false,
      newMessageText: undefined,
      hasNewMessage: false,
      currentUser: undefined,
      originMessages: [],
      offset: 0,
      loadingData: false,
    }),
    mounted() {
      const self = this;
      Echo.join(`user-status`)
        .here((users) => {
            users.forEach(user => {
              self.updateUserStatus(user.id, 1);
            });
        })
        .joining((user) => {
          self.updateUserStatus(user.id, 1);
        })
        .leaving((user) => {
          self.updateUserStatus(user.id, 0);
        });
      this.axios.get('/chat-messages/contacts').then((response) => {
        this.users = response.data;
        this.loading = false;
      });
      this.getMessages();
      setTimeout(() => {
        const container = this.$el.querySelector(".conversation-list .message-group:last-child");
        container.scrollIntoView({ block: 'end', behavior: 'auto' });
      }, 500);
      Echo.private(`${this.$route.params.id}-message`)
        .listen('MessageSentEvent', (e) => {
            self.originMessages.push(e.message);
            self.groupMessages();
        });
      Echo.join(`chat-typing`)
        .listenForWhisper('typing', (e) => {
          if (e.from === self.$route.params.id && e.to === self.currentUser.id && e.typing) {
            $('.typing').show();
          } else {
            $('.typing').hide();
          }
          setTimeout(() => {
            $('.typing').hide();
          }, 3000);
        });
    },
    watch: {
      '$route.params.id': function (id) {
        this.selectedUser = undefined;
        this.messages = [];
        this.originMessages = [];
        this.offset = 0;
        this.newMessageText = undefined;
        this.getMessages();
        const self = this;
          setTimeout(() => {
          const container = this.$el.querySelector(".conversation-list .message-group:last-child");
          container.scrollIntoView({ block: 'end', behavior: 'auto' });
        }, 500);
        Echo.private(`${id}-message`)
        .listen('MessageSentEvent', (e) => {
          self.originMessages.push(e.message);
          self.groupMessages();
        });
      }
    },
    computed: {
      selectedOption: function () {
        let optionText;
        switch (this.optionValue) {
          case 'recent':
            optionText = 'Recent';
            break;
          case 'oldest_unread_first':
            optionText = 'Oldest unread first';
            break;
          case 'unread_first':
            optionText = 'Unread first';
            break;
          default:
            optionText = '';
        }
        return optionText;
      },
    },
    beforeDestroy() {
      this.$el.querySelector('.conversation-list .messages').removeEventListener('scroll', this.handleDebouncedScroll);
    },
    methods: {
      handleDebouncedScroll: function(event) {
        const isUserScrolling = (event.target.scrollTop === 0);
        if (isUserScrolling && !this.loadingData) {
          this.getMessages();
          this.$el.querySelector('.conversation-list .messages').scrollTop = 10;
        }
      },
      updateUserStatus: function (userId, status) {
        let statusHolder = $(".status-holder-"+ userId);
        if (status == 1) {
            statusHolder.addClass('online');        
        } else {
          setTimeout(function () {            
            let last_seen = 'Last seen ' +
                getCalenderFormatForLastSeen(Date(), 'hh:mma', 0);
              
          }, 3000)
        }
      },
      getMessages: function() {
        this.loadingData = true;
        const user_id = this.$route.params.id;
        this.axios.get(`/chat-messages/${user_id}?offset=${this.offset}&limit=30`).then((response) => {
          this.selectedUser = response.data;
          this.currentUser = response.data.currentUser;
          this.originMessages = this.originMessages.concat(response.data.messages);
          if (this.offset === 0 && this.originMessages.length > 0) {
            setTimeout(() => {
              this.$el.querySelector('.conversation-list').addEventListener('scroll', this.handleDebouncedScroll);
            }, 1000);
          }
          this.offset = this.originMessages.length;
          this.groupMessages();
          this.loadingData = false;
        })
      },
      groupMessages: function() {
        const messages = this.originMessages.map((message) => {
          message.date = moment(message.created_at).startOf('day').unix();
          return message;
        });
        this.messages = _.chain(messages)
          .groupBy('date')
          .map((value, key) => ({ date: key, messages: value.reverse() }))
          .value();
        _.orderBy(this.messages, ['date'], ['DESC']);
      },
      changeSearchbarVisible: function () {
        this.userSearchVisible = !this.userSearchVisible;
        this.userSearchText = undefined;
      },
      onUserSearch: function(value) {
        this.userSearchText = value;
        this.axios.get(`/chat-messages/contacts?name=${value}&sort=${this.optionValue}`)
          .then((res) => {
            this.users = res.data;
          })
      },
      onOptionChanged: function (value) {
        this.optionValue = value;
        const filterQuery =  this.userSearchText ? 'name=' + this.userSearchText : '';
        this.axios.get(`/chat-messages/contacts?${filterQuery}&sort=${value}`)
          .then((res) => {
            this.users = res.data;
          })
      },
      getLogoFromName: function (username) {
        const names = username.split(' ');
        if (names.length === 1) {
          return username.slice(0, 2);
        }
        return names[0].slice(0, 1) + names[1].slice(0, 1);
      },
      clearMessages: function (receiver) {
        this.axios.delete(`/chat-messages/${receiver.id}`)
          .then((res) => {
            const { data } = res;
            if (data.status === 200) {
              const idx = this.users.findIndex(user => user.profile.id === receiver.id);
              this.users.splice(idx, 1);
            }
          })
      },
      clearSelectedUser: function () {
        this.$router.push('/messages');
      },
      addToFavourites: function () {
        this.selectedUser.isFavourite = !this.selectedUser.isFavourite;
      },
      muteNotification: function () {
        this.selectedUser.muted = !this.selectedUser.muted;
      },
      searchMessage: function () {
        this.selectedUser.showSearch = true;
      },
      changeMessageSearchVisible: function () {
        this.messageSearchVisible = !this.messageSearchVisible;
      },
      onInputNewMessage: function(e) {
        this.newMessageText = e.target.value;
        if (this.newMessageText) {
          this.hasNewMessage = true;
        }
        let channel = Echo.join(`chat-typing`);
        setTimeout(() => {
          channel.whisper('typing', {
            typing: true,
            from: this.currentUser.id,
            to: this.$route.params.id
          })
        }, 300);
      },
      sendMessage: function() {
        const self = this;
        this.axios.post('/chat-messages', {
          message: this.newMessageText,
          user_id: this.selectedUser.profile.id,
          name: this.selectedUser.profile.name
        })
          .then((response) => {
            this.originMessages.push(response.data.message)
            self.groupMessages();
            self.newMessageText = undefined;
          });
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/home.scss";
  @import "../../../sass/views/live-chat/details.scss";
</style>