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
                        <b-form-radio v-model="optionValue" name="some-radios" value="unread_first">Unread First</b-form-radio>
                      </b-dropdown-item>
                      <b-dropdown-item @click="onOptionChanged('oldest_unread_first')">
                        <b-form-radio v-model="optionValue" name="some-radios" value="oldest_unread_first">Oldest unread first</b-form-radio>
                      </b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item disabled>Mark all as read</b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item>Select</b-dropdown-item>
                    </b-dropdown>
                  </div>
                  <div class="no-users" v-if="!users.length">Nothing was found</div>
                  <ul class="user-list" v-if="users.length">
                    <li 
                      v-for="user in users"
                      :key="user.id"
                      :class="selectedUser && selectedUser.id === user.id ? 'selected' : ''"
                      @click="onSelectUser(user)"
                    >
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
                <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div class="coversation-tree">
                    <div class="conversations-start">
                      <div class="conversations-start__title" >Select any Conversation or send a New Message</div>
                      <button>New Message</button>
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
/**
 * Messages Dashboard View
 */
export default {
  //
  data: () => (
    {
      userSearchText: undefined,
      userSearchVisible: false,
      optionValue: 'unread_first',
      selectedUser: undefined,
      users: [
        {
          id: 1,
          name: 'Nat',
          logo: 'https://i.picsum.photos/id/565/200/200.jpg?hmac=QvKo8qgzFFNcZoXCpT0CNMDTwWd3ynwqLXxrzK2o8fw',
          userId: 'natcomedy',
          lastMessage: 'Sure wish there was more content here.',
          lastDate: 'Oct 13, 2020'
        },
        {
          id: 2,
          name: 'Lisa S.',
          logo: undefined,
          userId: 'u117945325',
          lastMessage: 'Sure wish there was more content here.',
          lastDate: 'Feb 16, 2021'
        },
        {
          id: 3,
          name: 'MCMXI',
          logo: undefined,
          userId: 'mcmxi',
          lastMessage: 'Any vids for sale ? 😳🙈',
          lastDate: 'Nov 20, 2020',
          muted: true,
        },
        {
          id: 4,
          name: 'Bjørn erik Bjørkhaug',
          logo: undefined,
          userId: 'u42082420',
          lastMessage: 'I want to see the full video with more details explanation',
          lastDate: 'June 19, 2020',
          expired: true,
        },
      ],
    }
  ),
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
    changeSearchbarVisible: function() {
      this.userSearchVisible = !this.userSearchVisible;
      this.userSearchText = undefined;
    },
    onOptionChanged: function (value) {
      this.optionValue = value;
    },
    getLogoFromName: function(username) {
      const names = username.split(' ');
      if (names.length === 1) {
        return username.slice(0, 2);
      }
      return names[0].slice(0, 1) + names[1].slice(0, 1);
    },
    onSelectUser: function(user) {
      this.selectedUser = user;
    }
  }
}
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat.scss";
</style>
