<template>
  <div class="col-md-4 col-sm-4 col-xs-4 messages-page-sidebar">
    <div class="top-bar" v-if="!userSearchVisible">
      <div class="pl-2">
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
      <b-dropdown class="filter-dropdown" id="user-filter-dropdown" right>
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
          <radio-group-box
            group_name="contacts-sort-options"
            value="recent"
            :checked="optionValue === 'recent'"
            label="Recent">
          </radio-group-box>
        </b-dropdown-item>
        <b-dropdown-item @click="onOptionChanged('unread_first')">
          <radio-group-box
            group_name="contacts-sort-options"
            value="unread_first"
            :checked="optionValue === 'unread_first'"
            label="Unread first">
          </radio-group-box>
        </b-dropdown-item>
        <b-dropdown-item @click="onOptionChanged('oldest_unread_first')">
          <radio-group-box
            group_name="contacts-sort-options"
            value="oldest_unread_first"
            :checked="optionValue === 'oldest_unread_first'"
            label="Oldest unread
            first">
          </radio-group-box>
        </b-dropdown-item>
        <b-dropdown-divider></b-dropdown-divider>
        <b-dropdown-item @click="markAllAsRead">Mark all as read</b-dropdown-item>
      </b-dropdown>
    </div>
    <swiper ref="listsGroupSwiper" class="lists-group" :options="swiperOptions">
      <swiper-slide class="lists-group-slide">
        <button class="lists-item" :class="!selectedPinnedList ? 'selected' : ''" @click="useAllContacts">
          All
        </button>
      </swiper-slide>
      <swiper-slide class="lists-group-slide" v-for="list in pinnedLists" :key="list.id">
        <button class="lists-item"  :class="selectedPinnedList && selectedPinnedList.id === list.id ? 'selected' : ''" :disabled="!list.users.length" @click="useContactsfromList(list)">
          {{list.name}}
        </button>
      </swiper-slide>
      <swiper-slide class="lists-group-slide">
        <button class="lists-item lists-group-add" @click="openPinToListsModal">
          <svg id="icon-edit" viewBox="0 0 24 24">
            <path d="M13.5 6.09L3 16.59V21h4.41l10.5-10.5zM6.12 19a1.12 1.12 0 0 1-.79-1.91l8.17-8.18 1.59 1.59-8.18 8.17a1.11 1.11 0 0 1-.79.33zM21 6.5a2.2 2.2 0 0 0-.64-1.56l-1.3-1.3a2.22 2.22 0 0 0-3.12 0L14.59 5 19 9.41l1.36-1.35A2.2 2.2 0 0 0 21 6.5z"></path>
          </svg>
        </button>
      </swiper-slide>
    </swiper>
    <div class="text-center" v-if="loading">
      <b-spinner variant="secondary" label="Loading..." small></b-spinner>
    </div>
    <div class="no-users" v-if="!users.length">Nothing was found</div>
    <ul class="user-list" v-if="users.length">
      <li v-for="user in users" :key="user.profile.id"
        :class="selectedUser && selectedUser.profile.id === user.profile.id ? 'selected' : ''"
      >
        <router-link :to="`/messages/${user.profile.id}`">
          <div class="user-content" :class="`user-${user.profile.id}`">
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
                  <span class="username">{{ user.profile.display_name ? user.profile.display_name : user.profile.name }}</span>
                </div>
                <b-dropdown class="filter-dropdown sidebar-more-dropdown" right>
                  <template #button-content>
                    <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                  </template>
                  <b-dropdown-item v-on:click.stop.prevent="clearMessages(user.profile)">
                    Clear conversation
                  </b-dropdown-item>
                </b-dropdown>
              </div>
              <div
                class="user-details-row"
                :key="user.last_message.id"
                v-if="!user.last_message.unread_messages_count"
              >
                <span class="last-message" v-if="!user.last_message.hasMediafile">{{ user.last_message.mcontent }}</span>
                <span class="last-message" v-if="user.last_message.hasMediafile">
                  <svg class="media-icon" viewBox="0 0 24 24">
                    <path d="M18,3H6A3,3,0,0,0,3,6V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V6A3,3,0,0,0,18,3Zm1,15a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H18a1,1,0,0,1,1,1ZM9,10.5A1.5,1.5,0,1,0,7.5,9,1.5,1.5,0,0,0,9,10.5ZM14,13l-3,3L9,14,6.85,16.15a.47.47,0,0,0-.14.35.5.5,0,0,0,.5.5h9.58a.5.5,0,0,0,.5-.5.47.47,0,0,0-.14-.35Z"></path>
                  </svg>
                  &middot;
                  {{ user.last_message.mcontent ? user.last_message.mcontent : 'media attachment' }}
                </span>
                <!-- Date  -->
                <span class="last-message-date">{{ getFuzzyFormat(moment(user.last_message.created_at).fromNow(true)) }}</span>
              </div>
              <div
                class="user-details-row is-unread"
                :key="user.last_message.id"
                v-if="user.last_message.unread_messages_count"
              >
                <span class="last-message">{{ `${user.last_message.unread_messages_count} new message${user.last_message.unread_messages_count > 1 ? 's' : ''}` }}</span>
                <span class="last-message-date">{{ getFuzzyFormat(moment(user.last_message.created_at).fromNow(true)) }}</span>
              </div>
            </div>
          </div>
          <div class="divider"></div>
        </router-link>
      </li>
    </ul>
    <b-modal hide-header centered hide-footer ref="pin-to-list-modal" title="Pin To List Modal">
      <div class="block-modal pin-to-list-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">PIN TO HOME</h4>
        </div>
        <div class="content">
          <div class="list-item" v-for="listItem in lists" :key="listItem.id" @click="!listItem.isPinned ? addListToPin(listItem) : removeListFromPin(listItem)">
            <round-check-box :value="listItem.isPinned" :key="listItem.isPinned"></round-check-box>
            <div class="list-item-content d-flex justify-content-between align-items-center">
              <div>
                <div class="title">{{listItem.name}}</div>
                <div class="content">{{listItem.users.length}} people</div>
              </div>
              <div class="avatars">
                <template v-for="user in listItem.users">
                  <div class="user-logo text-logo" v-if="!user.avatar" :key="user.id">
                    {{ getLogoFromName(user.name) }}
                  </div>
                  <div class="user-logo" v-if="user.avatar"  :key="user.id">
                    <img :src="user.avatar.filepath" alt="" />
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="closePinToListsModal">Close</button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
  import moment from 'moment';
  import _ from 'lodash';
  import { Swiper, SwiperSlide, directive } from 'vue-awesome-swiper';
  import Vuex from 'vuex';

  import RadioGroupBox from '@components/radioGroupBox';
  import RoundCheckBox from '@components/roundCheckBox';
  /**
   * Messages Sidebar View
   */
  export default {
    props: {
      selectedUser: undefined,
      proplists: Array,
      last_thread: undefined,
    },
    data: () => ({
      userSearchText: undefined,
      userSearchVisible: false,
      optionValue: 'unread_first',
      users: [],
      loading: true,
      moment: moment,
      originContacts: [],
      selectedPinnedList: undefined,
      lists: [],
      pinnedLists: [],
      swiperOptions: {
        lazy: true,
        slidesPerView: 'auto',
        observer: true,
        observeParents: true,
      },
    }),
    components: {
      Swiper,
      SwiperSlide,
      'radio-group-box': RadioGroupBox,
      'round-check-box': RoundCheckBox,
    },
    directives: {
      swiper: directive,
    },
    mounted() {
      this.axios.get('/lists')
        .then(res => {
          this.lists = res.data;
          this.pinnedLists = this.lists.filter(list => list.isPinned);
        });
      this.axios.get('/chat-messages/contacts').then((response) => {
        this.originContacts = response.data;
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
    watch: { 
      proplists: function(newVal) {
        this.lists = newVal.slice();
        this.pinnedLists = newVal.filter(list => list.isPinned);
      },
      last_thread: function(newVal) {
        if (newVal) {
          
          let index = this.users.findIndex(user => user.profile.id === newVal.sender_id);
          if (index < 0) {
            index = this.users.findIndex(user => user.profile.id === newVal.receiver_id);
          }
          const user = this.users[index];
          if (user) {
            const count = user.last_message.unread_messages_count;
            if (newVal.messages) {
              user.last_message = newVal.messages.pop();
              user.last_message.receiver_id = newVal.receiver_id;
              let hasMediafile = false;
              if (newVal.messages.length > 1 && newVal.messages[0].mediafile) {
                hasMediafile = true;
              }
              user.last_message.hasMediafile = hasMediafile;
            }
            if (newVal.unread_messages_count) {
              user.last_message.unread_messages_count = count ? count + 1 : 1;
            } else {
              user.last_message.unread_messages_count = 0;
            }
            this.users = [...this.users];
            this.users[index] = user;
          }
        }
      }
    },
    computed: {
      ...Vuex.mapGetters(['session_user']),
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
    created() { 
      this.getMe()
    },
    methods: {
      ...Vuex.mapActions([
        'getMe',
      ]),
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
      markAllAsRead: function() {
        this.axios.post('/chat-messages/mark-all-as-read');
      },
      useAllContacts: function () {
        this.users = this.originContacts.slice();
        this.selectedPinnedList = undefined;
      },
      useContactsfromList: function(list) {
        this.selectedPinnedList = list;
        const contacts = this.originContacts.slice();
        _.remove(contacts, function(o) { return list.users.findIndex(user => user.id === o.profile.id) < 0; });
        this.users = contacts;
      },
      openPinToListsModal: function() {
        this.axios.get('/lists')
          .then(res => {
            this.lists = res.data;
            this.$refs['pin-to-list-modal'].show();
          });
      },
      closePinToListsModal: function() {
        this.$refs['pin-to-list-modal'].hide();
      },
      addListToPin: function(list) {
        this.axios.post(`/lists/${list.id}/pin`).then(() => {
          const newPin = this.pinnedLists.slice();
          newPin.push(list);
          this.pinnedLists = newPin;
          const newLists = this.lists.slice();
          const idx = newLists.findIndex(item => item.id === list.id);
          newLists[idx].isPinned = true;
          this.lists = newLists;
        });
      },
      removeListFromPin: function(list) {
        this.axios.delete(`/lists/${list.id}/pin`).then(() => {
          const newPin = this.pinnedLists.slice();
          const index = newPin.findIndex(item => item.id === list.id);
          newPin.splice(index, 1);
          this.pinnedLists = newPin;
          const newLists = this.lists.slice();
          const idx = newLists.findIndex(item => item.id === list.id);
          newLists[idx].isPinned = false;
          this.lists = newLists;
        });
      },
      getFuzzyFormat: function(value) {
        if (new RegExp(` seconds`).test(value) || new RegExp(`a minute`).test(value)) {
          return '1m';
        }
        if (new RegExp(` minutes`).test(value)) {
          return value.replace(` minutes`, `m`);
        }
        if (new RegExp(`an hour`).test(value)) {
          return '1h';
        }
        if (new RegExp(` hours`).test(value)) {
          return value.replace(` hours`, `h`);
        }
        if (new RegExp(`a day`).test(value)) {
          return '1d';
        }
        if (new RegExp(` days`).test(value)) {
          return value.replace(` days`, `d`);
        }
        if (new RegExp(`a week`).test(value)) {
          return '1w';
        }
        if (new RegExp(` weeks`).test(value)) {
          return value.replace(` weeks`, `w`);
        }
        if (new RegExp(`a month`).test(value)) {
          return '1m';
        }
        if (new RegExp(` months`).test(value)) {
          return value.replace(` months`, `m`);
        }
        if (new RegExp(` years`).test(value)) {
          return value.replace(` years`, `y`);
        }
        if (new RegExp(`a year`).test(value)) {
          return '1y';
        }
      },
      clearMessages: function (receiver) {
        this.axios.delete(`/chat-messages/${receiver.id}`)
          .then(() => {
            const idx = this.users.findIndex(user => user.profile.id === receiver.id);
            this.users.splice(idx, 1);
            this.$router.push('/messages');
          })
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/sidebar.scss";
</style>