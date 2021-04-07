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
                      <b-dropdown-item @click="markAllAsRead">Mark all as read</b-dropdown-item>
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
                <div v-if="!selectedUser" class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div class="coversation-tree">
                    <div class="conversations-start">
                      <div class="conversations-start__title">Select any Conversation or send a New Message</div>
                       <router-link to="/messages/new"><button>New Message</button></router-link>
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
      users: [],
      loading: true,
      moment: moment,
    }),
    mounted() {
      // Fetch Users' Online/Away status
      const self = this;
      Echo.join(`user-status`)
        .joining((user) => {
          self.updateUserStatus(user.id, 1);
        })
        .leaving((user) => {
          self.updateUserStatus(user.id, 0);
        });
      this.axios.get('/chat-messages/contacts').then((response) => {
        this.users = response.data;
        this.users.forEach(user => {
          if (user.profile.user.is_online) {
            setTimeout(() => {
              this.updateUserStatus(user.profile.user.id, 1);
            }, 2000);
          }
        });
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
      updateUserStatus: function (userId, status) {
        let statusHolder = $(".status-holder-"+ userId);
        if (status == 1) {
          statusHolder.addClass('online');
        } else {
          statusHolder.removeClass('online');
        }
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
      markAllAsRead: function() {
        this.axios.post('/chat-messages/mark-all-as-read');
      },
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/home.scss";
</style>