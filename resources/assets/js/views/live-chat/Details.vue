<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg" />
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-body nopadding">
              <div class="row message-box">
                <chat-sidebar :selectedUser="selectedUser" />
                <div v-if="selectedUser" class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div :class="messageSearchVisible ? 'conversation-header no-border' : 'conversation-header'">
                    <button class="back-btn btn" type="button" @click="clearSelectedUser">
                      <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </button>
                    <div class="content">
                      <div class="user-name">
                        <span>{{ selectedUser.profile.display_name ? `${selectedUser.profile.display_name} (${selectedUser.profile.name})` : selectedUser.profile.name }}</span>
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
                          <font-awesome-icon :icon="selectedUser.profile.hasLists ? ['fas', 'star'] : ['far', 'star']" />
                        </button>
                        <div class="v-divider"></div>
                        <button class="notification-btn btn" type="button" @click="muteNotification()">
                          <font-awesome-icon :icon="selectedUser.profile.muted ? ['far', 'bell-slash'] : ['far', 'bell'] " />
                        </button>
                        <div class="v-divider"></div>
                        <button class="gallery-btn btn" type="button" @click="goToGallery">
                          <font-awesome-icon :icon="['far', 'image']" />&nbsp;&nbsp;Gallery
                        </button>
                        <div class="v-divider"></div>
                        <button class="search-btn btn" type="button" @click="changeMessageSearchVisible">
                          <font-awesome-icon :icon="['fas', 'search']" />&nbsp;&nbsp;Find
                        </button>
                      </div>
                    </div>
                    <!-- More Dropdown Menu -->
                    <b-dropdown id="more-dropdown" right>
                      <template #button-content>
                        <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                      </template>
                      <b-dropdown-item disabled>
                        Copy link to profile
                      </b-dropdown-item>
                      <b-dropdown-item @click="showListModal">
                        Add to / remove from lists
                      </b-dropdown-item>
                      <b-dropdown-item v-if="!selectedUser.profile.is_follow_for_free" @click="setFollowForFree(selectedUser.profile.id)">
                        Follow for free
                      </b-dropdown-item>
                      <b-dropdown-item disabled>
                        Give user a discount
                      </b-dropdown-item>
                      <b-dropdown-item @click="editCustomName">
                        Rename @{{ selectedUser.profile.username }}
                      </b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item disabled>Hide chat</b-dropdown-item>
                      <b-dropdown-item v-if="!selectedUser.profile.muted" @click="muteNotification">Mute notifications</b-dropdown-item>
                      <b-dropdown-item v-if="selectedUser.profile.muted" @click="muteNotification">Unmute notifications</b-dropdown-item>
                      <b-dropdown-divider></b-dropdown-divider>
                      <b-dropdown-item class="block-item" disabled>Restrict @{{ selectedUser.profile.username }}</b-dropdown-item>
                      <b-dropdown-item @click="showBlockModal" class="block-item">Block @{{ selectedUser.profile.username }}</b-dropdown-item>
                      <b-dropdown-item class="block-item">Report @{{ selectedUser.profile.username }}</b-dropdown-item>
                    </b-dropdown>
                  </div>
                  <div class="details message-search" v-if="messageSearchVisible">
                    <div class="top-bar user-search-bar">
                      <button class="btn" type="button" @click="changeMessageSearchVisible">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </button>
                      <b-form-input v-model="messageSearchText" placeholder="Find in chat"></b-form-input>
                      <div class="search-results d-none">
                          <span class="search-results-count">{{currentSearchIndex + 1}} / {{ totalSearches.length }}</span>
                        <button class="btn" type="button" :disabled="currentSearchIndex  >= totalSearches.length - 1" @click="onShowNextSearch">
                          <svg id="icon-arrow-up" viewBox="0 0 24 24">
                            <path d="M12 7.25l-6.87 6.88a1 1 0 0 0-.3.7 1 1 0 0 0 1 1 1 1 0 0 0 .71-.29L12 10.08l5.46 5.46a1 1 0 0 0 .71.29 1 1 0 0 0 1-1 1 1 0 0 0-.3-.7z"></path>
                          </svg>
                        </button>
                        <button class="btn" type="button" :disabled="currentSearchIndex < 1" @click="onShowPrevSearch">
                          <svg id="icon-arrow-down" viewBox="0 0 24 24">
                            <path d="M12 16.75L5.13 9.87a1 1 0 0 1-.3-.7 1 1 0 0 1 1-1 1 1 0 0 1 .71.29L12 13.92l5.46-5.46a1 1 0 0 1 .71-.29 1 1 0 0 1 1 1 1 1 0 0 1-.3.7z"></path>
                          </svg>
                        </button>
                      </div>
                      <button class="btn" type="button" @click="onMessageSearchTextChange">
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
                            <div class="received" v-if="currentUser && currentUser.id !== message.sender_id">
                              <div class="user-logo text-logo" v-if="selectedUser && !selectedUser.profile.avatar">
                                {{ getLogoFromName(selectedUser.name) }}
                              </div>
                              <div class="user-logo" v-if="selectedUser && selectedUser.profile.avatar">
                                <img :src="selectedUser.profile.avatar.filepath" alt="" />
                              </div>
                              <div class="content">
                                <div class="text" :class="`message-${msg.id}`" v-for="msg in message.messages" :key="msg.id">{{ msg.mcontent }}</div>
                                <div class="time">{{ moment(message.created_at).format('hh:mm A') }}</div>
                              </div>
                            </div>
                            <div class="sent" v-if="currentUser && currentUser.id === message.sender_id">
                              <div class="text" :class="`message-${msg.id}`" v-for="msg in message.messages" :key="msg.id">{{ msg.mcontent }}</div>
                              <div class="time">{{ moment(message.created_at).format('hh:mm A') }}</div>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                    <div class="typing dot-pulse" style="display: none">...</div>
                  </div>
                  <div class="conversation-footer">
                    <div class="swiper-slider" v-if="sortableImgs.length > 0">
                      <div v-if="isDragListVisible" >
                        <draggable class="sort-change-div" v-model="sortableImgs" :group="'column.components'" handle=".handle" ghost-class="ghost">
                          <div v-for="(element, index) in sortableImgs" :key="index" class="drag-element">
                            <div class="img-wrapper">
                              <img :src="element.src" alt="" />
                              <span v-if="!element.selected"  class="unchecked-circle" @click="onSelectSortableImg(index, true)"></span>
                              <span v-if="element.selected" class="checked-circle" @click="onSelectSortableImg(index, false)">{{element.order}}</span>
                            </div>
                            <div class="handle">
                              <svg class="icon-drag" viewBox="0 0 24 24">    
                                <path d="M6 7a2 2 0 102 2 2 2 0 00-2-2zm12 4a2 2 0 10-2-2 2 2 0 002 2zm-6-4a2 2 0 102 2 2 2 0 00-2-2zm6 6a2 2 0 102 2 2 2 0 00-2-2zm-6 0a2 2 0 102 2 2 2 0 00-2-2zm-6 0a2 2 0 102 2 2 2 0 00-2-2z"></path>
                              </svg>
                            </div>
                          </div>
                        </draggable>
                        <div class="sort-action-btns">
                          <div>
                            <button :disabled="!applyBtnEnabled" class="btn arrows-btn" @click="applyImgsSort">
                              <svg id="icon-arrow-left" viewBox="0 0 24 24">
                                <path d="M7.25 12l6.88-6.87a1 1 0 0 1 .7-.3 1 1 0 0 1 1 1 1 1 0 0 1-.29.71L10.08 12l5.46 5.46a1 1 0 0 1 .29.71 1 1 0 0 1-1 1 1 1 0 0 1-.7-.3z"></path>
                              </svg>
                              <svg id="icon-arrow-right" viewBox="0 0 24 24"> 
                                <path d="M16.75 12l-6.88 6.87a1 1 0 0 1-.7.3 1 1 0 0 1-1-1 1 1 0 0 1 .29-.71L13.92 12 8.46 6.54a1 1 0 0 1-.29-.71 1 1 0 0 1 1-1 1 1 0 0 1 .7.3z"></path>
                              </svg>
                            </button>
                          </div>
                          <button class="btn confirm-btn" @click="confirmImgsSort">
                            <svg id="icon-done" viewBox="0 0 24 24">
                              <path d="M9 19.42l-5.71-5.71A1 1 0 0 1 3 13a1 1 0 0 1 1-1 1 1 0 0 1 .71.29L9 16.59l10.29-10.3A1 1 0 0 1 20 6a1 1 0 0 1 1 1 1 1 0 0 1-.29.71z"></path>
                            </svg>
                          </button>
                        </div>
                      </div>
                      <swiper ref="mySwiper" :options="swiperOptions" :key="sortableImgs.length">
                        <swiper-slide class="slide">
                          <div v-if="!isDragListVisible">
                            <div class="swiper-image-wrapper" v-for="(img, index) in sortableImgs" :key="index">
                              <img v-preview:scope-a class="swiper-lazy" :src="img.src" />
                              <div class="icon-close" @click="removeSortableImg(index)">
                                <svg viewBox="0 0 24 24">
                                  <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
                                </svg>
                              </div>
                            </div>
                            <button class="slider-btn arrows-btn" @click="isDragListVisible = true">
                              <svg id="icon-arrow-left" viewBox="0 0 24 24">
                                <path d="M7.25 12l6.88-6.87a1 1 0 0 1 .7-.3 1 1 0 0 1 1 1 1 1 0 0 1-.29.71L10.08 12l5.46 5.46a1 1 0 0 1 .29.71 1 1 0 0 1-1 1 1 1 0 0 1-.7-.3z"></path>
                              </svg>
                              <svg id="icon-arrow-right" viewBox="0 0 24 24"> 
                                <path d="M16.75 12l-6.88 6.87a1 1 0 0 1-.7.3 1 1 0 0 1-1-1 1 1 0 0 1 .29-.71L13.92 12 8.46 6.54a1 1 0 0 1-.29-.71 1 1 0 0 1 1-1 1 1 0 0 1 .7.3z"></path>
                              </svg>
                            </button>
                            <button class="slider-btn" @click="addNewMedia"><span>+</span></button>
                          </div>
                        </swiper-slide>
                      </swiper>
                    </div>
                    <textarea placeholder="Type a message" name="text" rows="1" maxlength="10000"
                      spellcheck="false" :value="newMessageText" @input="onInputNewMessage"></textarea>
                    <div class="action-btns">
                      <div>
                        <!-- image -->
                        <input
                          type="file"
                          id="image-upload-btn"
                          @change="onMediaChanged"
                          accept="image/x-png,image/gif,image/jpeg"
                          ref="imagesUpload"
                          multiple
                          @click="activeMediaRef = $refs.imagesUpload"
                        />
                        <label for="image-upload-btn" class="btn action-btn">
                          <svg id="icon-media" viewBox="0 0 24 24">
                            <path d="M18,3H6A3,3,0,0,0,3,6V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V6A3,3,0,0,0,18,3Zm1,15a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H18a1,1,0,0,1,1,1ZM9,10.5A1.5,1.5,0,1,0,7.5,9,1.5,1.5,0,0,0,9,10.5ZM14,13l-3,3L9,14,6.85,16.15a.47.47,0,0,0-.14.35.5.5,0,0,0,.5.5h9.58a.5.5,0,0,0,.5-.5.47.47,0,0,0-.14-.35Z" fill="#8a96a3"></path>
                          </svg>
                        </label>
                        <!-- video -->
                        <input
                          type="file"
                          disabled
                          id="video-upload-btn"
                          accept="video/mp4,video/x-m4v,video/*"
                          ref="videosUpload"
                          @click="activeMediaRef = $refs.videosUpload"
                        />
                        <label for="video-upload-btn" class="btn action-btn" disabled>
                          <svg id="icon-video" viewBox="0 0 24 24">
                            <path
                              d="M21.79 6a1.21 1.21 0 0 0-.86.35L19 8.25V7a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-1.25l1.93 1.93a1.22 1.22 0 0 0 2.07-.86V7.18A1.21 1.21 0 0 0 21.79 6zM17 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1zm4-2.08l-1.34-1.34a2.25 2.25 0 0 1 0-3.16L21 9.08z"
                              fill="#8a96a3"></path>
                          </svg>
                        </label>
                        <!-- microphone -->
                        <button class="btn action-btn" type="button" disabled>
                          <svg id="icon-voice" viewBox="0 0 24 24">
                            <path
                              d="M12 15a4 4 0 0 0 4-4V6a4 4 0 0 0-8 0v5a4 4 0 0 0 4 4zm-2-9a2 2 0 0 1 4 0v5a2 2 0 0 1-4 0zm9 4a1 1 0 0 0-1 1 6 6 0 0 1-12 0 1 1 0 0 0-2 0 8 8 0 0 0 7 7.93V21a1 1 0 0 0 2 0v-2.07A8 8 0 0 0 20 11a1 1 0 0 0-1-1z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- Medis from vault -->
                        <button class="btn action-btn" type="button" disabled>
                          <svg id="icon-vault" viewBox="0 0 24 24">
                            <path
                              d="M20.33,5.69h0l-.9-1.35A3,3,0,0,0,16.93,3H7.07a3,3,0,0,0-2.5,1.34l-.9,1.35A4,4,0,0,0,3,7.91V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.91A4,4,0,0,0,20.33,5.69ZM6.24,5.45A1,1,0,0,1,7.07,5h9.86a1,1,0,0,1,.83.45l.37.55H5.87ZM19,18a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V8H19ZM9.5,12.75A1.25,1.25,0,1,0,8.25,11.5,1.25,1.25,0,0,0,9.5,12.75ZM7.93,17h8.14a.42.42,0,0,0,.3-.73L13.7,13.6l-2.55,2.55-1.7-1.7L7.63,16.27a.42.42,0,0,0,.3.73Z"
                              fill="#8a96a3"></path>
                          </svg></button>
                        <!-- message price -->
                        <button class="btn action-btn" type="button" disabled>
                          <svg id="icon-price" viewBox="0 0 24 24">
                            <path
                              d="M22 13a3.38 3.38 0 0 0-1-2.4l-7.41-7.43A4.06 4.06 0 0 0 10.76 2H5a3 3 0 0 0-3 3v5.76a4 4 0 0 0 1.17 2.83L10.6 21a3.4 3.4 0 0 0 4.8 0l5.6-5.6a3.38 3.38 0 0 0 1-2.4zm-2.4 1L14 19.6a1.45 1.45 0 0 1-2 0l-7.41-7.43A2 2 0 0 1 4 10.76V5a1 1 0 0 1 1-1h5.76a2 2 0 0 1 1.41.59L19.6 12a1.4 1.4 0 0 1 0 2zM7.7 6a1.7 1.7 0 1 0 1.7 1.7A1.7 1.7 0 0 0 7.7 6zm6.16 6.28c-1-.22-1.85-.3-1.85-.78s.43-.51 1.06-.51a1.2 1.2 0 0 1 .92.43.48.48 0 0 0 .35.16h1.35a.23.23 0 0 0 .21-.22c0-.71-.86-1.55-2-1.84v-.75a.27.27 0 0 0-.26-.27h-1.27a.27.27 0 0 0-.26.27v.74a2.31 2.31 0 0 0-2 2c0 2.81 4.07 1.85 4.07 2.89 0 .48-.47.53-1.27.53a1.3 1.3 0 0 1-1-.52.66.66 0 0 0-.4-.17h-1.28a.23.23 0 0 0-.2.22c0 1 1 1.72 2.08 2v.74a.27.27 0 0 0 .26.27h1.25a.26.26 0 0 0 .26-.27v-.71A2.18 2.18 0 0 0 16 14.43c0-1.2-.86-1.88-2.14-2.15z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                      </div>
                      <button class="send-btn btn" :disabled="!(hasNewMessage || sortableImgs.length > 0)" type="button" @click="sendMessage">
                        <b-spinner v-if="isSendingFiles" small></b-spinner>
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
    <b-modal v-if="selectedUser" hide-header centered hide-footer ref="block-modal" title="Block User Modal">
      <div class="block-modal">
        <h4>Block @{{ selectedUser.profile.username }}</h4>
        <div class="content">
          <radio-group-box
            group_name="block_reason"
            value="block"
            @onChange="selectBlockReason"
            label="Block user from accessing your profile.">
          </radio-group-box>
          <radio-group-box
            group_name="block_reason"
            value="restrict"
            @onChange="selectBlockReason"
            label="Restrict, user will not be able to send you direct messages or reply to your posts.">
          </radio-group-box>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeBlockModal">Cancel</button>
          <button class="link-btn" @click="confirmBlockReason" :disabled="!blockReason">Confirm</button>
        </div>
      </div>
    </b-modal>
    <b-modal v-if="selectedUser" hide-header centered hide-footer ref="custom-name-modal" title="Custom Name Modal">
      <div class="block-modal">
        <h4>RENAME @{{ selectedUser.profile.username }}</h4>
        <div class="content mb-3 mt-3">
          <b-form-input v-model="userCustomName" placeholder="Custom name"></b-form-input>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeCustomNameModal">Cancel</button>
          <button class="link-btn" @click="saveCustomName" :disabled="!userCustomName">Save</button>
        </div>
      </div>
    </b-modal>
    <b-modal v-if="selectedUser" hide-header centered hide-footer ref="list-edit-modal" title="List Edit Modal">
      <div class="block-modal list-edit-modal">
        <div class="header d-flex justify-content-between align-items-center">
          <h4>Save to List</h4>
          <b-dropdown class="filter-dropdown" id="list-sort-dropdown" right>
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
            <b-dropdown-item @click="getListsBySort('name', sortDir)">
              <radio-group-box
                group_name="list-sort-options"
                value="name"
                :checked="sortWith === 'name'"
                label="Name">
              </radio-group-box>
            </b-dropdown-item>
            <b-dropdown-item @click="getListsBySort('recent', sortDir)">
              <radio-group-box
                group_name="list-sort-options"
                value="recent"
                :checked="sortWith === 'recent'"
                label="Recent">
              </radio-group-box>
            </b-dropdown-item>
            <b-dropdown-item @click="getListsBySort('people', sortDir)">
              <radio-group-box
                group_name="list-sort-options"
                value="people"
                :checked="sortWith === 'people'"
                label="People">
              </radio-group-box>
            </b-dropdown-item>
            <b-dropdown-divider></b-dropdown-divider>
            <b-dropdown-item @click="getListsBySort(sortWith, 'asc')">
              <radio-group-box
                group_name="list-sort-directions"
                value="asc"
                :checked="sortDir === 'asc'"
                label="Ascendening">
              </radio-group-box>
            </b-dropdown-item>
            <b-dropdown-item @click="getListsBySort(sortWith, 'desc')">
              <radio-group-box
                group_name="list-sort-directions"
                value="desc"
                :checked="sortDir === 'desc'"
                label="Descending">
              </radio-group-box>
            </b-dropdown-item>
          </b-dropdown>
        </div>
        <div class="content border-box mb-1 mt-1">
          <div class="text-center mb-3 mt-3 text-muted" v-if="!isListUpdating && !lists.length">
            <em>No Available List. Please add a new list.</em>
          </div>
          <div class="list-item" v-for="listItem in lists" :key="listItem.id" @click="onListChanged(listItem)">
            <round-check-box :value="isUserInList(listItem)" :key="isUserInList(listItem)"></round-check-box>
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
          <div class="text-center" v-if="isListUpdating">
            <b-spinner variant="secondary" label="Loading..." small></b-spinner>
          </div>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="addNewList">+ New List</button>
          <button class="link-btn" @click="closeListModal">Close</button>
        </div>
      </div>
    </b-modal>
    <b-modal v-if="selectedUser" hide-header centered hide-footer ref="new-list-modal" title="New List Modal">
      <div class="block-modal">
        <h4>CREATE NEW LIST</h4>
        <div class="content mb-3 mt-3">
          <b-form-input v-model="newListName" placeholder="New list name"></b-form-input>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeNewListModal">Cancel</button>
          <button class="link-btn" @click="saveNewListName" :disabled="!newListName">Save</button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
  import moment from 'moment';
  import _ from 'lodash';
  import { Swiper, SwiperSlide, directive } from 'vue-awesome-swiper';
  import PhotoSwipe from 'photoswipe/dist/photoswipe';
  import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default';
  import createPreviewDirective from 'vue-photoswipe-directive';
  import draggable from 'vuedraggable';

  import RadioGroupBox from '@components/radioGroupBox';
  import RoundCheckBox from '@components/roundCheckBox';
  import Sidebar from './Sidebar';

  /**
   * Messages Dashboard View
   */
  export default {
    props: {
      session_user: null,
    },
    data: () => ({
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
      messageSearchText: undefined,
      currentSearchIndex: -1,
      totalSearches: [],
      blockReason: undefined,
      muted: undefined,
      userCustomName: undefined,
      isListUpdating: false,
      listOption: undefined,
      lists: [],
      newListName: undefined,
      sortDir: '',
      sortWith: 'name',
      swiperOptions: {
        lazy: true,
        slidesPerView: 'auto',
        observer: true,
        observeParents: true,
      },
      activeMediaRef: null,
      isDragListVisible: false,
      sortableImgs: [],
      applyBtnEnabled: false,
      isSendingFiles: false,
      hasMore: true,
    }),
    mounted() {
      const self = this;
      // Mark unread messages as read
      this.axios.post(`/chat-messages/${this.$route.params.id}/mark-as-read`);
      this.getMessages();
      this.findConversationList();
      Echo.private(`${this.$route.params.id}-message`)
        .listen('MessageSentEvent', (e) => {
          if (e.message.receiver_id === self.currentUser.id) {
            console.log('------ e.message:', e.message);
            self.originMessages.unshift(e.message);
            self.offset += 1;
            self.groupMessages();
            $('.conversation-list').animate({ scrollTop: $('.conversation-list')[0].scrollHeight }, 500);
          }
        });
      // Echo.join(`user-status`)
      //   .joining((user) => {
      //     self.updateUserStatus(user.id, 1);
      //   })
      //   .leaving((user) => {
      //     self.updateUserStatus(user.id, 0);
      //   });
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

        // Mark unread messages as read
        this.axios.post(`/chat-messages/${this.$route.params.id}/mark-as-read`);

        this.getMessages();
        const self = this;
        this.findConversationList();
        Echo.private(`${id}-message`)
        .listen('MessageSentEvent', (e) => {
          if (e.message.receiver_id === self.currentUser.id) {
            self.originMessages.unshift(e.message);
            self.offset += 1;
            self.groupMessages();
            $('.conversation-list').animate({ scrollTop: $('.conversation-list')[0].scrollHeight }, 500);
          }
        });
      }
    },
    components: {
      Swiper,
      SwiperSlide,
      draggable,
      'radio-group-box': RadioGroupBox,
      'round-check-box': RoundCheckBox,
      'chat-sidebar': Sidebar,
    },
    directives: {
      swiper: directive,
      preview: createPreviewDirective({
          showAnimationDuration: 0,
          bgOpacity: 0.75
        }, PhotoSwipe, PhotoSwipeUI)
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
        if (isUserScrolling && !this.loadingData && this.hasMore) {
          this.getMessages();
          this.$el.querySelector('.conversation-list .messages').scrollTop = 10;
        }
      },
      findConversationList: function() {
        setTimeout(() => {
            if (this.$el.querySelector('.conversation-list')) {
              $('.conversation-list').animate({ scrollTop: $('.conversation-list')[0].scrollHeight }, 500);
              this.$el.querySelector('.conversation-list').addEventListener('scroll', this.handleDebouncedScroll);
            } else {
              this.findConversationList();
            }
          }, 1000);
      },
      getMessages: async function() {
        this.loadingData = true;
        const user_id = this.$route.params.id;
        const response = await this.axios.get(`/chat-messages/${user_id}?offset=${this.offset}&limit=10`);
        this.selectedUser = response.data;
        if (!this.currentUser) {
          this.currentUser = response.data.currentUser;
        }
        if (response.data.messages.length === 0) {
          this.hasMore = false;
        }
        this.originMessages = response.data.messages.concat(this.originMessages);
        this.offset = this.originMessages.length;
        this.groupMessages();
        this.loadingData = false;
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
        this.messages = _.cloneDeep(this.messages);
        this.selectedUser = { ...this.selectedUser, messages: this.messages };
      },
      getLogoFromName: function (username) {
        const names = username.split(' ');
        if (names.length === 1) {
          return username.slice(0, 2);
        }
        return names[0].slice(0, 1) + names[1].slice(0, 1);
      },
      clearSelectedUser: function () {
        this.$router.push('/messages');
      },
      addToFavourites: function () {
        this.showListModal();
      },
      muteNotification: async function () {
        if (!this.selectedUser.profile.muted) {
          await this.axios.patch(`/chat-messages/${this.$route.params.id}/mute`);
        } else {
          await this.axios.patch(`/chat-messages/${this.$route.params.id}/unmute`);
        }
        this.selectedUser = { ...this.selectedUser, profile: { ...this.selectedUser.profile, muted: !this.selectedUser.profile.muted } };
      },
      onMessageSearchTextChange: function () {
        this.clearHighlightMessages();
        this.axios.get(`/chat-messages/${this.$route.params.id}/search?query=${this.messageSearchText}`)
          .then((response) => {
            this.totalSearches = response.data;
            this.currentSearchIndex = -1;
            $('.user-search-bar .search-results').removeClass('d-none');
          });
      },
      changeMessageSearchVisible: function () {
        this.messageSearchVisible = !this.messageSearchVisible;
        $('.user-search-bar .search-results').addClass('d-none');
        this.currentSearchIndex = -1;
        this.messageSearchText = undefined;
        this.clearHighlightMessages();
      },
      onInputNewMessage: function(e) {
        this.newMessageText = e.target.value;
        if (this.newMessageText) {
          this.hasNewMessage = true;
        } else {
          this.hasNewMessage = false;
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
        // Sending Text Message
        if (this.newMessageText) {
          this.axios.post('/chat-messages', {
            message: this.newMessageText,
            user_id: this.selectedUser.profile.id,
          })
            .then((response) => {
              this.originMessages.unshift(response.data.message);
              this.groupMessages();
              this.newMessageText = undefined;
              $('.conversation-list').animate({ scrollTop: $('.conversation-list')[0].scrollHeight }, 500);
            });
        }
        // Sending media files
        if (this.sortableImgs.length > 0) {
          const files = this.sortableImgs.map(img => img.file);
          this.isSendingFiles = true;
          // const mediafilesLinks = [];
          const promises = [];
          files.map(file => {
            const data = new FormData();
            data.append('mediafile', file);
            data.append('mftype', 'vault');
            const promise = this.axios.post('/mediafiles', data)
              .then((res) => {
                this.axios.post('/chat-messages', {
                  media_id: res.data.mediafile.id,
                  user_id: this.selectedUser.profile.id,
                });
              });
            promises.push(promise);
              // mediafilesLinks.push(res.data.mediafile.filepath);
              // await this.axios.post('/chat-messages', {
              //   media: mediafilesLinks,
              //   user_id: this.selectedUser.profile.id,
              //   name: this.selectedUser.profile.name,
              // });
          });
          const self = this;
          Promise.all(promises).then(function() {
            self.isSendingFiles = false;
            self.sortableImgs = [];
          });
        }
      },
      onShowNextSearch: function() {
        if (this.currentSearchIndex < this.totalSearches.length) {
          this.clearHighlightMessages();
          this.currentSearchIndex++;
          this.showMessageWithId(this.totalSearches[this.currentSearchIndex]);
        }
      },
      onShowPrevSearch: function() {
        if (this.currentSearchIndex > 0) {
          this.currentSearchIndex--;
          this.clearHighlightMessages();
          this.showMessageWithId(this.totalSearches[this.currentSearchIndex]);
        }
      },
      showMessageWithId: async function(messageId) {
        this.loadingData = true;
        const user_id = this.$route.params.id;

        let index = this.originMessages.findIndex(message => message.id === messageId);
        while (index < 0) {
          await this.axios.get(`/chat-messages/${user_id}?offset=${this.offset}&limit=10`).then((response) => {
            this.originMessages = this.originMessages.concat(response.data.messages);
            this.offset = this.originMessages.length;
          });
          const allMessages = [];
          this.originMessages.forEach(message => {
            allMessages = allMessages.concat(message.messages);
          });
          index = allMessages.findIndex(msg => msg.id === messageId);
        }
        const el = $(`.message-${messageId}.text`);
        el.html(el.html().replace(new RegExp(this.messageSearchText, 'gi'), (str) => `<span class="highlight">${str}</span>`));
        const newPos = $('.conversation-list').scrollTop() + $(`.message-${messageId}.text`).height() + $(`.message-${messageId}.text`).offset().top - $('.conversation-list').height() - $('.conversation-list').offset().top;
        $('.conversation-list').animate({scrollTop: newPos}, 500);
        this.loadingData = false;
      },
      clearHighlightMessages: function() {
        const el = $('.highlight').parent();
        el.html(el.text());
      },
      showBlockModal: function() {
        this.$refs['block-modal'].show();
      },
      closeBlockModal: function() {
        this.$refs['block-modal'].hide();
        this.blockReason = undefined;
      },
      selectBlockReason: function(value) {
        this.blockReason = value;
      },
      confirmBlockReason: function() {
        const self = this;
        if (this.blockReason === 'block') {
          this.axios.patch(`/users/${this.currentUser.id}/settings`, { blocked: [{ slug: this.selectedUser.profile.username }] })
            .then(() => {
              self.closeBlockModal();
              self.$router.push('/messages');
            });
        }
      },
      editCustomName: function() {
        this.$refs['custom-name-modal'].show();
      },
      closeCustomNameModal: function() {
        this.$refs['custom-name-modal'].hide();
        this.userCustomName = undefined;
      },
      saveCustomName: function() {
        const self = this;
        if (this.userCustomName) {
          this.axios.post(`/chat-messages/${this.$route.params.id}/custom-name`, { name: this.userCustomName })
            .then(() => {
              this.selectedUser = {
                ...this.selectedUser,
                profile: {
                  ...this.selectedUser.profile,
                  display_name: this.userCustomName,
                }
              };
              const newUsers = this.users.slice();
              const index = newUsers.findIndex(user => user.profile.id === this.selectedUser.profile.id);
              newUsers[index].profile = this.selectedUser.profile;
              this.users = newUsers;
              this.closeCustomNameModal();
            });
        }
      },
      showListModal: async function() {
        this.$refs['list-edit-modal'].show();
        this.isListUpdating = true;
        this.sortDir = '';
        this.sortWith = '';
        this.axios.get('/lists')
          .then(res => {
            this.lists = res.data;
            this.isListUpdating = false;
          });
      },
      closeListModal: function() {
        this.$refs['list-edit-modal'].hide();
      },
      onListChanged: function(list) {
        if (!this.isUserInList(list)) {
          this.addUserToList(list.id);
        } else {
          this.removeUserFromList(list.id);
        }
      },
      addNewList: function() {
        this.closeListModal();
        this.$refs['new-list-modal'].show();
        this.newListName = undefined;
      },
      isUserInList: function(listItem) {
        if (listItem.users) {
          return listItem.users.findIndex(user => user.id === this.selectedUser.profile.id) > -1;
        }
        return false;
      },
      closeNewListModal: function() {
        this.$refs['new-list-modal'].hide();
        this.showListModal();
      },
      saveNewListName: async function() {
        const res = await this.axios.post('/lists', { name: this.newListName })
        this.lists.push(res.data);
        this.closeNewListModal();
      },
      addUserToList: function(id) {
        this.axios.post(`/lists/${id}/users`, { user: this.selectedUser.profile.id })
          .then((res) => {
            const newLists = this.lists.slice();
            const index = newLists.findIndex(list => list.id === id);
            newLists[index] = res.data;
            this.lists = newLists;
            let hasLists = false;
            newLists.forEach(list => {
              if (this.isUserInList(list)) {
                hasLists = true;
              }
            });
            this.selectedUser = { ...this.selectedUser, profile: { ...this.selectedUser.profile, hasLists: hasLists } };
          });
      },
      removeUserFromList: function(id) {
        this.axios.delete(`/lists/${id}/users/${this.selectedUser.profile.id }`)
          .then((res) => {
            const newLists = this.lists.slice();
            const index = newLists.findIndex(list => list.id === id);
            newLists[index] = res.data;
            this.lists = newLists;
            let hasLists = false;
            newLists.forEach(list => {
              if (this.isUserInList(list)) {
                hasLists = true;
              }
            });
            this.selectedUser = { ...this.selectedUser, profile: { ...this.selectedUser.profile, hasLists: hasLists } };
          });
      },
      goToGallery: function() {
        this.$router.push(`/messages/${this.selectedUser.profile.id}/gallery`);
      },
      getListsBySort: function(sortWith, sortDir) {
        this.sortWith = sortWith;
        this.sortDir = sortDir;
        this.axios.get(`/lists?sort=${sortWith}&dir=${sortDir}`)
          .then((res) => {
            this.lists = res.data;
          });
      },
      addNewMedia: function() {
        this.activeMediaRef.click();
      },
      removeSortableImg: function(index) {
        const newArr = this.sortableImgs.slice();
        newArr.splice(index, 1);
        
        this.sortableImgs = newArr;
        if (this.$refs.mySwiper) {
          this.$refs.mySwiper.updateSwiper();
        }
      },
      onSelectSortableImg: function(index, status) {
        const newArr = this.sortableImgs.slice();
        newArr[index].selected = status;
        const sortedArr = _.orderBy(newArr, ['order'], ['asc']);
        let order = 0;
        sortedArr.forEach(item => {
          if (item.selected) { 
            order++;
            const idx = newArr.findIndex(it => it.src === item.src);
            newArr[idx].order = order;
          }
        });
        this.sortableImgs = newArr;
        this.applyBtnEnabled = true;
      },
      applyImgsSort: function() {
        const newArr = this.sortableImgs.slice();
        const sortedArr = _.orderBy(newArr, ['order'], ['asc']);
        sortedArr.forEach(item => {
          item.order = undefined;
          item.selected = undefined;
        });
        this.sortableImgs = sortedArr;
        this.applyBtnEnabled = false;
      },
      confirmImgsSort: function() {
        this.applyImgsSort();
        this.isDragListVisible = false;
      },
      onMediaChanged: function(e) {
        const files = _.values(e.target.files);
        files.forEach(file => {
          this.sortableImgs.push({
            src: URL.createObjectURL(file),
            file,
          });
        });
        if (this.$refs.mySwiper) {
          this.$refs.mySwiper.updateSwiper();
        }
      }, 
      setFollowForFree: function(userId) {
        this.axios.patch(`/users/${userId}/settings`, {
          is_follow_for_free: true,
        }).then(() => {
          this.selectedUser = {
            ...this.selectedUser,
            profile: {
              ...this.selectedUser.profile,
              is_follow_for_free: true,
            }
          };
        });
      },
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat/details_scoped.scss";
</style>
<style lang="scss">
  @import "../../../sass/views/live-chat/details.scss";
</style>
