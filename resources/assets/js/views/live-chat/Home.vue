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
                      <button class="btn" type="button">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                  <div class="top-bar user-search-bar" v-if="userSearchVisible">
                    <button class="btn" type="button" @click="changeSearchbarVisible">
                      <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                    <b-form-input v-model="userSearchText" placeholder="Search"></b-form-input>
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
                  <div class="no-users" v-if="!users.length">Nothing was found</div>
                  <ul class="user-list" v-if="users.length">
                    <li v-for="user in users" :key="user.id"
                      :class="selectedUser && selectedUser.id === user.id ? 'selected' : ''"
                      @click="onSelectUser(user)">
                      <div class="user-content">
                        <div class="user-logo text-logo" v-if="!user.logo">
                          {{ getLogoFromName(user.name) }}
                        </div>
                        <div class="user-logo" v-if="user.logo">
                          <img :src="user.logo" alt="" />
                        </div>
                        <div class="user-details">
                          <div class="user-details-row">
                            <div>
                              <span class="username">{{ user.name }}</span>
                              <span class="user-id">{{ `@${user.userId}` }}</span>
                            </div>
                            <!-- Close Button -->
                            <button class="close-btn btn" type="button">
                              <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                          </div>
                          <div class="user-details-row">
                            <span class="last-message">{{ user.lastMessage }}</span>
                            <!-- Date  -->
                            <span class="last-message-date">{{ user.lastDate }}</span>
                          </div>
                        </div>
                      </div>
                      <div class="divider"></div>
                    </li>
                  </ul>
                </div>
                <div v-if="!selectedUser" class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div class="coversation-tree">
                    <div class="conversations-start">
                      <div class="conversations-start__title">Select any Conversation or send a New Message</div>
                      <button>New Message</button>
                    </div>
                  </div>
                </div>
                <div v-if="selectedUser" class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div :class="messageSearchVisible ? 'conversation-header no-border' : 'conversation-header'">
                    <button class="back-btn btn" type="button" @click="clearSelectedUser">
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                    <div class="content">
                      <div class="user-name">
                        <span>{{ selectedUser.name }}</span>
                      </div>
                      <div class="details" v-if="!messageSearchVisible">
                        <div class="online-status" v-if="!selectedUser.isOnline">Last seen {{ selectedUser.lastOnline }}
                        </div>
                        <div class="online-status" v-if="selectedUser.isOnline">
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
                      <b-dropdown-item>Restrict @{{ selectedUser.userId }}</b-dropdown-item>
                      <b-dropdown-item>Block @{{ selectedUser.userId }}</b-dropdown-item>
                      <b-dropdown-item>Report @{{ selectedUser.userId }}</b-dropdown-item>
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
                      <div class="message-group" :key="messageGroup.date"  v-for="messageGroup in messages">
                        <div class="message-group-time"><span>{{ messageGroup.date }}</span></div>
                        <template v-for="message in messageGroup.messages">
                          <div class="message" :key="message.id">
                            <div class="received" v-if="selectedUser && selectedUser.id === message.user.id">
                              <div class="user-logo text-logo" v-if="selectedUser && !selectedUser.logo">
                                {{ getLogoFromName(selectedUser.name) }}
                              </div>
                              <div class="user-logo" v-if="selectedUser && selectedUser.logo">
                                <img :src="selectedUser.logo" alt="" />
                              </div>
                              <div class="content">
                                <div class="text">{{ message.text }}</div>
                                <div class="time">{{ message.time }}</div>
                              </div>
                            </div>
                            <div class="sent" v-if="selectedUser && selectedUser.id !== message.user.id">
                              <div class="text">{{ message.text }}</div>
                              <div class="time">{{ message.time }}</div>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                  <div class="conversation-footer">
                    <textarea placeholder="Type a message" name="text" rows="1" maxlength="10000"
                      spellcheck="false"></textarea>
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
                      <button class="send-btn btn" disabled type="button">
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
      users: [{
          id: 1,
          name: 'Nat',
          logo: 'https://i.picsum.photos/id/565/200/200.jpg?hmac=QvKo8qgzFFNcZoXCpT0CNMDTwWd3ynwqLXxrzK2o8fw',
          userId: 'natcomedy',
          lastMessage: 'Sure wish there was more content here.',
          lastDate: 'Oct 13, 2020',
          isOnline: false,
          lastOnline: 'Feb 16',
        },
        {
          id: 2,
          name: 'Lisa S.',
          logo: undefined,
          userId: 'u117945325',
          lastMessage: 'Sure wish there was more content here.',
          lastDate: 'Feb 16',
          isOnline: false,
          lastOnline: 'Feb 17',
        },
        {
          id: 3,
          name: 'MCMXI',
          logo: undefined,
          userId: 'mcmxi',
          lastMessage: 'Any vids for sale ? 😳🙈',
          lastDate: 'Nov 20, 2020',
          muted: true,
          isOnline: true,
        },
        {
          id: 4,
          name: 'Bjørn erik Bjørkhaug',
          logo: undefined,
          userId: 'u42082420',
          lastMessage: 'I want to see the full video with more details explanation',
          lastDate: 'June 19, 2020',
          expired: true,
          isOnline: true
        },
      ],
      messages: [
        {
          date: moment('2021-2-13').format('MMM DD, YYYY'),
          messages: [
            {
              id: 1,
              text: 'Hello, how are you?',
              time: '10:29 PM',
              user: {
                id: 2,
                name: 'Lisa S.'
              },
            },
            {
              id: 2,
              text: 'Hey, I am fine. And you?',
              time: '10:39 PM',
              user: {
                id: 3,
                name: 'MCMXI'
              },
            },
          ]
        },
        {
          date: moment('2021-2-16').format('MMM DD, YYYY'),
          messages: [
            {
              id: 3,
              time: '08:29 PM',
              text: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
              user: {
                id: 2,
                name: 'Lisa S.'
              },
            },
            {
              id: 4,
              text: 'Ok, thanks.',
              time: '09:29 PM',
              user: {
                id: 3,
                name: 'MCMXI'
              },
            }
          ]
        }
      ],
      messageSearchVisible: false
    }),
    mounted() {
      this.axios.get('/chat-messages').then((response) => {
        console.log('-- response data', response);
      })
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
    methods: {
      changeSearchbarVisible: function () {
        this.userSearchVisible = !this.userSearchVisible;
        this.userSearchText = undefined;
      },
      onOptionChanged: function (value) {
        this.optionValue = value;
      },
      getLogoFromName: function (username) {
        const names = username.split(' ');
        if (names.length === 1) {
          return username.slice(0, 2);
        }
        return names[0].slice(0, 1) + names[1].slice(0, 1);
      },
      onSelectUser: function (user) {
        this.selectedUser = user;
      },
      clearSelectedUser: function () {
        this.selectedUser = undefined;
      },
      addToFavourites: function () {
        this.selectedUser.isFavourite = !this.selectedUser.isFavourite;
      },
      muteNotification: function () {
        this.selectedUser.muted = !this.selectedUser.muted;
      },
      searchMessage: function () {
        console.log('------ search');
        this.selectedUser.showSearch = true;
      },
      changeMessageSearchVisible: function () {
        this.messageSearchVisible = !this.messageSearchVisible;
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat.scss";
</style>