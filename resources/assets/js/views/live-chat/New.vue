<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg" />
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
                  <div class="top-bar user-search-bar">
                    <button class="btn" type="button" disabled>
                      <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                    <b-form-input :value="userSearchText" @input="onChangeSearchText" placeholder="Add People"></b-form-input>
                    <button v-if="userSearchText" class="btn" type="button" @click="changeSearchbarVisible">
                      <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                  </div>
                  <div class="options-bar">
                    <span class="selected-option">{{selectedOption}}</span>
                    <div>
                      <button class="btn" @click="openFilterModal">
                        <svg class="sort-icon filter-icon" aria-hidden="true" viewBox="0 0 24 24">
                          <path
                            d="M3 18a1 1 0 001 1h5v-2H4a1 1 0 00-1 1zM3 6a1 1 0 001 1h9V5H4a1 1 0 00-1 1zm10 14v-1h7a1 1 0 000-2h-7v-1a1 1 0 00-2 0v4a1 1 0 002 0zM7 10v1H4a1 1 0 000 2h3v1a1 1 0 002 0v-4a1 1 0 00-2 0zm14 2a1 1 0 00-1-1h-9v2h9a1 1 0 001-1zm-5-3a1 1 0 001-1V7h3a1 1 0 000-2h-3V4a1 1 0 00-2 0v4a1 1 0 001 1z">
                          </path>
                        </svg>
                      </button>
                      <b-dropdown class="filter-dropdown" right>
                        <template #button-content>
                          <svg class="sort-icon" viewBox="0 0 24 24">
                            <path
                              d="M4 19h4a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1zM3 6a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1zm1 7h10a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1z">
                            </path>
                          </svg>
                        </template>
                        <b-dropdown-item @click="onOptionChanged('recent')">
                          <radio-group-box
                            group_name="users-sort-options"
                            value="recent"
                            :checked="optionValue === 'recent'"
                            label="Recent">
                          </radio-group-box>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onOptionChanged('name')">
                          <radio-group-box
                            group_name="users-sort-options"
                            value="name"
                            :checked="optionValue === 'name'"
                            label="Name">
                          </radio-group-box>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onOptionChanged('online')">
                          <radio-group-box
                            group_name="users-sort-options"
                            value="online"
                            :checked="optionValue === 'online'"
                            label="Online">
                          </radio-group-box>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onOptionChanged('offline')">
                          <radio-group-box
                            group_name="users-sort-options"
                            value="offline"
                            :checked="optionValue === 'offline'"
                            label="Offline">
                          </radio-group-box>
                        </b-dropdown-item>
                        <b-dropdown-divider></b-dropdown-divider>
                        <b-dropdown-item @click="onDirectionChanged('asc')"> 
                          <radio-group-box
                            group_name="users-sort-directions"
                            value="asc"
                            :checked="directionValue === 'asc'"
                            label="Ascending"> 
                          </radio-group-box>
                        </b-dropdown-item>
                        <b-dropdown-item @click="onDirectionChanged('desc')">
                          <radio-group-box
                            group_name="users-sort-directions"
                            value="desc"
                            :checked="directionValue === 'desc'"
                            label="Descending">  
                          </radio-group-box>
                        </b-dropdown-item>
                      </b-dropdown>
                    </div>
                  </div>
                  <div class="user-list-container">
                    <ul class="user-list">
                      <div class="text-center" v-if="loading">
                        <b-spinner variant="secondary" label="Loading..." size="small"></b-spinner>
                      </div>
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
                    <div class="scheduled-message-head" v-if="scheduledMessageDate">
                      <div>
                        <svg class="icon-schedule" viewBox="0 0 24 24">
                          <path d="M19 3h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V5a2 2 0 0 0-2-2zm0 15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9h14zm0-11H5V5h14zM9.79 17.21a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .29-.71 1 1 0 0 0-1-1 1 1 0 0 0-.71.29l-4.29 4.3-1.29-1.3a1 1 0 0 0-.71-.29 1 1 0 0 0-1 1 1 1 0 0 0 .29.71z"></path>
                        </svg>
                        <span> Scheduled for </span>
                        <strong>{{ moment(scheduledMessageDate).format('MMM DD, h:mm a') }}</strong>
                      </div>
                      <button class="btn close-btn" @click="clearSchedule">
                        <svg class="icon-close" viewBox="0 0 24 24">
                          <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
                        </svg>
                      </button>
                    </div>
                    <textarea placeholder="Type a message" name="text" rows="1" maxlength="10000"
                      spellcheck="false" v-model="messageText" @keydown="onCheckReturnKey"></textarea>
                    <div class="action-btns">
                      <div>
                        <!-- image --> 
                        <button class="btn action-btn" disabled type="button">
                          <svg id="icon-media" viewBox="0 0 24 24"> <path d="M18,3H6A3,3,0,0,0,3,6V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V6A3,3,0,0,0,18,3Zm1,15a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H18a1,1,0,0,1,1,1ZM9,10.5A1.5,1.5,0,1,0,7.5,9,1.5,1.5,0,0,0,9,10.5ZM14,13l-3,3L9,14,6.85,16.15a.47.47,0,0,0-.14.35.5.5,0,0,0,.5.5h9.58a.5.5,0,0,0,.5-.5.47.47,0,0,0-.14-.35Z" fill="#8a96a3"></path> </svg>
                        </button>
                        <!-- video -->
                        <button class="btn action-btn" disabled type="button">
                          <svg id="icon-video" viewBox="0 0 24 24">
                            <path
                              d="M21.79 6a1.21 1.21 0 0 0-.86.35L19 8.25V7a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-1.25l1.93 1.93a1.22 1.22 0 0 0 2.07-.86V7.18A1.21 1.21 0 0 0 21.79 6zM17 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1zm4-2.08l-1.34-1.34a2.25 2.25 0 0 1 0-3.16L21 9.08z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- microphone -->
                        <button class="btn action-btn" disabled type="button">
                          <svg id="icon-voice" viewBox="0 0 24 24">
                            <path
                              d="M12 15a4 4 0 0 0 4-4V6a4 4 0 0 0-8 0v5a4 4 0 0 0 4 4zm-2-9a2 2 0 0 1 4 0v5a2 2 0 0 1-4 0zm9 4a1 1 0 0 0-1 1 6 6 0 0 1-12 0 1 1 0 0 0-2 0 8 8 0 0 0 7 7.93V21a1 1 0 0 0 2 0v-2.07A8 8 0 0 0 20 11a1 1 0 0 0-1-1z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button> 
                        <!-- Medis from vault -->
                        <button class="btn action-btn" disabled type="button">
                          <svg id="icon-vault" viewBox="0 0 24 24">
                            <path
                              d="M20.33,5.69h0l-.9-1.35A3,3,0,0,0,16.93,3H7.07a3,3,0,0,0-2.5,1.34l-.9,1.35A4,4,0,0,0,3,7.91V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.91A4,4,0,0,0,20.33,5.69ZM6.24,5.45A1,1,0,0,1,7.07,5h9.86a1,1,0,0,1,.83.45l.37.55H5.87ZM19,18a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V8H19ZM9.5,12.75A1.25,1.25,0,1,0,8.25,11.5,1.25,1.25,0,0,0,9.5,12.75ZM7.93,17h8.14a.42.42,0,0,0,.3-.73L13.7,13.6l-2.55,2.55-1.7-1.7L7.63,16.27a.42.42,0,0,0,.3.73Z"
                              fill="#8a96a3"></path>
                          </svg></button>
                        <button
                          class="btn action-btn"
                          type="button"
                          @click="openScheduleMessageModal"
                        >
                          <svg class="icon-schedule" viewBox="0 0 24 24">
                            <path d="M19 3h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V5a2 2 0 0 0-2-2zm0 15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9h14zm0-11H5V5h14zM9.79 17.21a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .29-.71 1 1 0 0 0-1-1 1 1 0 0 0-.71.29l-4.29 4.3-1.29-1.3a1 1 0 0 0-.71-.29 1 1 0 0 0-1 1 1 1 0 0 0 .29.71z"></path>
                          </svg>
                        </button>
                        <!-- message price -->
                        <button class="btn action-btn" disabled type="button">
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
    <!-- Filter Modal -->
    <b-modal hide-header centered hide-footer ref="users-filter-modal" id="users-filter-modal" title="User Filter Modal">
      <div class="block-modal users-filter-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">FILTER SUBSCRIBERS</h4>
        </div>
        <div class="content">
          <div class="list-item">
            <round-check-box :value="filterOptions.totalSpent" :key="filterOptions.totalSpent"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div class="list-item-title" @click="onFilterOptionChanged('totalSpent', filterOptions.totalSpent ? undefined : 100)">Total spent</div>
              <!-- counter -->
              <counter
                min="100"
                max="10000"
                step="100"
                prefix="$"
                @onchange="(val) => onFilterOptionChanged('totalSpent', val)"
              />
            </div>
          </div>
          <div class="list-item">
            <round-check-box :value="filterOptions.tippedOver" :key="filterOptions.tippedOver"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div class="list-item-title" @click="onFilterOptionChanged('tippedOver', filterOptions.tippedOver ? undefined : 10)">Tipped over</div>
              <!-- counter -->
              <counter
                min="10"
                max="10000"
                step="10"
                prefix="$"
                @onchange="(val) => onFilterOptionChanged('tippedOver', val)"
              />
            </div>
          </div>
          <div class="list-item">
            <round-check-box :value="filterOptions.subscribedOver" :key="filterOptions.subscribedOver"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div class="list-item-title" @click="onFilterOptionChanged('subscribedOver', filterOptions.subscribedOver ? undefined : 1)">Subscribed over</div>
              <!-- counter -->
              <counter
                min="1"
                max="12"
                step="1"
                suffix="month"
                @onchange="(val) => onFilterOptionChanged('subscribedOver', val)"
              />
            </div>
          </div>
          <div class="list-item">
            <round-check-box :value="filterOptions.inactiveOver" :key="filterOptions.inactiveOver"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div
                class="list-item-title"
                @click="onFilterOptionChanged('inactiveOver', filterOptions.inactiveOver ? undefined : 1)"
              >
                Inactive over
              </div>
              <!-- counter -->
              <counter
                min="1"
                max="30"
                step="1"
                suffix="day"
                @onchange="(val) => onFilterOptionChanged('inactiveOver', val)"
              />
            </div>
          </div>
          <div class="list-item">
            <round-check-box :value="filterOptions.showRecommendations" :key="filterOptions.showRecommendations"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div
                class="list-item-title"
                @click="onFilterOptionChanged('showRecommendations', filterOptions.showRecommendations ? undefined : true)"
              >
                Show only recommendations
              </div>
            </div>
          </div>

          <div class="list-item">
            <round-check-box :value="filterOptions.signupAfterDate" :key="filterOptions.signupAfterDate"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div
                class="list-item-title"
                @click="onFilterOptionChanged('signupAfterDate', filterOptions.signupAfterDate ? undefined : signupAfterDate)"
              >
                Who signed up after
              </div>
            </div>
          </div>
          <b-form-datepicker
            v-model="signupAfterDate"
            placeholder="Choose a date"
            :class="filterOptions.signupAfterDate ? '' : 'disabled'"
            :max="signupBeforeDate ? signupBeforeDate : new Date()">
          </b-form-datepicker>

          <div class="list-item">
            <round-check-box :value="filterOptions.signupBeforeDate" :key="filterOptions.signupBeforeDate"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div
                class="list-item-title"
                @click="onFilterOptionChanged('signupBeforeDate', filterOptions.signupBeforeDate ? undefined : signupBeforeDate)"
              >Who signed up before</div>
            </div>
          </div>
        </div>
        <!-- Date Picker -->
        <b-form-datepicker
          v-model="signupBeforeDate"
          placeholder="Choose a date"
          :class="filterOptions.signupBeforeDate ? '' : 'disabled'"
          :min="signupAfterDate" :max="new Date()">
        </b-form-datepicker>
        <br />
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="closeFilterModal">Cancel</button>
          <button class="link-btn" @click="closeFilterModal" disabled>Reset</button>
          <button class="link-btn" @click="closeFilterModal">Apply</button>
        </div>
      </div>
    </b-modal>
    <b-modal modal-class="schedule-message-modal" hide-header centered hide-footer ref="schedule-message-modal">
      <div class="block-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">SCHEDULED MESSAGES</h4>
        </div>
        <div class="content">
          <b-form-datepicker
            v-model="scheduledMessage.date"
            class="mb-3 mt-1"
            :min="new Date()"
          />
          <b-form-timepicker v-model="scheduledMessage.time" class="mb-2" locale="en"></b-form-timepicker>
        </div>
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="clearSchedule">Cancel</button>
          <button class="link-btn" @click="applySchedule" :disabled="!scheduledMessage.date || !scheduledMessage.time">Apply</button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
  import _ from 'lodash';
  import moment from 'moment';

  import RadioGroupBox from '@components/radioGroupBox';
  import RoundCheckBox from '@components/roundCheckBox';
  import Counter from '@components/Counter';
  /**
   * Messages Dashboard View
   */
  export default {
    //
    data: () => ({
      userSearchText: undefined,
      userSearchVisible: false,
      optionValue: undefined,
      directionValue: undefined,
      selectedUser: undefined,
      messageText: undefined,
      users: [],
      filteredUsers: [],
      selectedUsers: [],
      loading: true,
      signupAfterDate: new Date(),
      signupBeforeDate: new Date(),
      moment: moment,
      filterOptions: {},
      scheduledMessage: {},
      scheduledMessageDate: null,
    }),
    mounted() {
      this.axios.get('/chat-messages/users').then((response) => {
        const { users } = response.data;
        this.users = users;
        this.filteredUsers = users.slice();
        this.loading = false;
      });
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
          case 'online':
            optionText = 'Online';
            break;
          case 'offline':
            optionText = 'Offline';
            break;
          default:
            optionText = 'Recent';
        }
        return optionText;
      },
    },
    components: {
      'round-check-box': RoundCheckBox,
      'radio-group-box': RadioGroupBox,
      'counter': Counter,
    },
    methods: {
      changeSearchbarVisible: function () {
        this.userSearchText = undefined;
        this.filteredUsers = this.users.slice();
      },
      onOptionChanged: function (value) {
        this.optionValue = value;
        this.loading = true;
        this.axios.get(`/chat-messages/users?sort=${value}&dir=${this.directionValue}`).then((response) => {
          const { users } = response.data;
          this.users = users;
          this.filteredUsers = users.slice();
          this.loading = false;
        });
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
        const self = this;
        const promises = [];
        this.selectedUsers.forEach(user => {
          const promise = this.axios.post('/chat-messages', {
            message: this.messageText,
            user_id: user.id,
            name: user.name,
          });
          promises.push(promise);
        })
        Promise.all(promises).then(function() {
          if (self.selectedUsers.length > 1) {
            self.$router.push('/messages');
          } else {
            self.$router.push(`/messages/${self.selectedUsers[0].id}`);
          }
        });
      },
      onDirectionChanged: function(dir) {
        this.directionValue = dir;
        this.loading = true;
        this.axios.get(`/chat-messages/users?sort=${this.optionValue}&dir=${dir}`).then((response) => {
          const { users } = response.data;
          this.users = users;
          this.filteredUsers = users.slice();
          this.loading = false;
        });
      },
      openFilterModal: function() {
        this.$refs['users-filter-modal'].show();
      },
      closeFilterModal: function() {
        this.$refs['users-filter-modal'].hide();
      },
      onFilterOptionChanged: function(option, value) {
        this.filterOptions[option] = value;
        this.filterOptions = { ...this.filterOptions };
      },
      onCheckReturnKey: function(e) {
        if (e.ctrlKey && e.keyCode == 13) {
          this.sendMessage();
        }
      },
      openScheduleMessageModal: function() {
        this.$refs['schedule-message-modal'].show();
      },
      applySchedule: function() {
        this.scheduledMessageDate = moment().unix();
        this.$refs['schedule-message-modal'].hide();
        this.scheduledMessage = {};
      },
      clearSchedule: function() {
        this.scheduledMessageDate = undefined;
        this.scheduledMessage = {};
        this.$refs['schedule-message-modal'].hide();
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/new.scss";
</style>
<style lang="scss">
#users-filter-modal {
  .modal-dialog {
    max-width: 380px;
  }
  .b-form-btn-label-control.form-control > .btn {
    color: #8a96a3;
  }
  .b-form-datepicker {
    padding: 3px;
    &.disabled {
      border-color: rgba(138,150,163,.3);
      & > label, & > .btn {
        color: rgba(138,150,163,.3);
      }
    }
    &.show {
      border-color: rgba(138,150,163, 1);
      color: rgba(138,150,163, 1);
    }
  } 
  .list-item {
    display: flex;
    align-items: center;
    min-height: 48px;
    margin-top: 3px;
    
    .list-item-content {
      flex: 1;
      margin-left: 12px;

      .list-item-title {
        cursor: pointer;
      }
    }

    .action-btns {
      .link-btn {
        border: none;
        font-size: 14px;
        color: #fefefe;
        font-weight: 500;
        min-width: 78px;
        transition: opacity .15s ease,background-color .15s ease,box-shadow .15s ease;
        display: inline-block;
        white-space: nowrap;
        text-align: center;
        text-transform: uppercase;
        padding: 9px 16px;
        border-radius: 18px;
        color: #00aff0;
        background-color: transparent;
      
        &:disabled {
          background-color: transparent;
          background: none;
          color: #8a96a3;
          opacity: .4;
        }
      }
    }
  }
}
</style>