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
                  <div class="top-bar">
                    <div>
                      <router-link to="/messages">
                        <button class="btn" type="button">
                          <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        </button>
                      </router-link>
                      <span class="top-bar-title">New Message</span>
                    </div>
                  </div>
                  <div class="options-bar">
                    <span class="selected-option">SEND TO</span>
                  </div>
                  <div class="top-bar user-search-bar">
                    <button class="btn" type="button" disabled>
                      <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                    <b-form-input :value="userSearchText" @input="onChangeSearchText" placeholder="Add People"></b-form-input>
                    <button class="btn" type="button" @click="changeSearchbarVisible">
                      <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                  </div>
                  <div class="options-bar">
                    <span class="selected-option">{{selectedOption}}</span>
                    <div>
                      <button class="btn">
                        <svg class="sort-icon filter-icon" aria-hidden="true" viewBox="0 0 24 24">
                          <path
                            d="M3 18a1 1 0 001 1h5v-2H4a1 1 0 00-1 1zM3 6a1 1 0 001 1h9V5H4a1 1 0 00-1 1zm10 14v-1h7a1 1 0 000-2h-7v-1a1 1 0 00-2 0v4a1 1 0 002 0zM7 10v1H4a1 1 0 000 2h3v1a1 1 0 002 0v-4a1 1 0 00-2 0zm14 2a1 1 0 00-1-1h-9v2h9a1 1 0 001-1zm-5-3a1 1 0 001-1V7h3a1 1 0 000-2h-3V4a1 1 0 00-2 0v4a1 1 0 001 1z">
                          </path>
                        </svg>
                      </button>
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
                        <b-dropdown-item @click="onOptionChanged('name')">
                          <b-form-radio v-model="optionValue" name="some-radios" value="name">Name</b-form-radio>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onOptionChanged('available')">
                          <b-form-radio v-model="optionValue" name="some-radios" value="available">Available</b-form-radio>
                        </b-dropdown-item>
                        <b-dropdown-divider></b-dropdown-divider>
                        <b-dropdown-item @click="onOptionChanged('ASC')"> 
                          <b-form-radio v-model="optionValue" name="some-radios" value="ASC">Ascending</b-form-radio>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onOptionChanged('DESC')">
                          <b-form-radio v-model="optionValue" name="some-radios" value="DESC">Descending</b-form-radio>
                        </b-dropdown-item>
                      </b-dropdown>
                    </div>
                  </div>

                  <div class="user-list-container">
                    <ul class="user-list">
                      <li v-for="user in filteredUsers" :key="user.id"
                        :class="selectedUser && selectedUser.id === user.id ? 'selected' : ''"
                        @click="onSelectUser(user)"  
                      >
                        <div class="user-content">
                          <div class="user-logo text-logo" v-if="!user.avatar">
                            {{ getLogoFromName(user.name) }}
                          </div>
                          <div class="user-logo" v-if="user.avatar">
                            <img :src="user.avatar.filepath" alt="" />
                          </div>
                          <div class="user-details">
                            <div>
                              <div class="username">{{ user.name }}</div>
                              <div class="user-id">{{ `@${user.username}` }}</div>
                            </div>
                            <!-- Radio Button -->
                            <round-check-box :key="selectedUsers.findIndex(u => u.id === user.id) > -1" :value="selectedUsers.findIndex(u => u.id === user.id) > -1"></round-check-box>
                          </div>
                        </div>
                        <div class="divider"></div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div :class="'conversation-header'">
                    <div class="content" v-if="selectedUsers.length === 0">
                      SELECT USERS TO SEND THEM A MESSAGE 
                    </div>
                    <div class="content" v-if="selectedUsers.length > 0">
                      NEW MESSAGE TO {{ selectedUsers.length }} USERS
                    </div>
                  </div>
                  <div class="conversation-list">
                  </div>
                  <div class="conversation-footer">
                    <textarea placeholder="Type a message" name="text" rows="1" maxlength="10000"
                      spellcheck="false" v-model="messageText"></textarea>
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
                      <button class="send-btn btn" :disabled="!messageText" @click="sendMessage" type="button">
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
  import RoundCheckBox from '../../components/roundCheckBox';
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
      messageText: undefined,
      users: [],
      filteredUsers: [],
      selectedUsers: [],
    }),
    mounted() {
      this.axios.get('/chat-messages/users').then((response) => {
        const { following, followers } = response.data;
        this.users = followers.concat(following);
        this.filteredUsers = this.users.slice();
      })
    },
    computed: {
      selectedOption: function () {
        let optionText;
        switch (this.optionValue) {
          case 'recent':
            optionText = 'Recent';
            break;
          case 'name':
            optionText = 'Name';
            break;
          case 'available':
            optionText = 'Available';
            break;
          default:
            optionText = 'Recent';
        }
        return optionText;
      },
    },
    components: {
      'round-check-box': RoundCheckBox
    },
    methods: {
      changeSearchbarVisible: function () {
        this.userSearchText = undefined;
        this.filteredUsers = this.users.slice();
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
        const idx = this.selectedUsers.findIndex(u => u.id === user.id);
        if (idx > -1) {
          this.selectedUsers.splice(idx, 1);
        } else {
          this.selectedUsers.push(user);
        }
      },
      onChangeSearchText: function(value) {
        this.userSearchText = value;
        this.filteredUsers = this.users.filter(user => user.username.toLowerCase().indexOf(value.toLowerCase()) > -1 || user.name.toLowerCase().indexOf(value.toLowerCase()) > -1);
      },
      sendMessage: function() {
        const promises = [];
        this.selectedUsers.forEach(user => {
          const promise = this.axios.post('/chat-messages', { message: this.messageText, user: user.id });
          promises.push(promise);
        })
        Promise.all(promises).then(function(values) {
          console.log(values);
        });

      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/new.scss";
</style>