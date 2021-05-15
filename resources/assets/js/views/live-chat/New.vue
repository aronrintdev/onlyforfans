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
                  <div
                    class="conversation-footer"
                    :class="messagePrice ? 'price-view': ''" v-if="!showAudioRec"
                  >
                    <div v-if="messagePrice" class="price-to-view-header d-flex align-items-center justify-content-between">
                      <div class="price-to-view-title">
                        <svg viewBox="0 0 24 24">
                          <path
                            d="M22 13a3.38 3.38 0 0 0-1-2.4l-7.41-7.43A4.06 4.06 0 0 0 10.76 2H5a3 3 0 0 0-3 3v5.76a4 4 0 0 0 1.17 2.83L10.6 21a3.4 3.4 0 0 0 4.8 0l5.6-5.6a3.38 3.38 0 0 0 1-2.4zm-2.4 1L14 19.6a1.45 1.45 0 0 1-2 0l-7.41-7.43A2 2 0 0 1 4 10.76V5a1 1 0 0 1 1-1h5.76a2 2 0 0 1 1.41.59L19.6 12a1.4 1.4 0 0 1 0 2zM7.7 6a1.7 1.7 0 1 0 1.7 1.7A1.7 1.7 0 0 0 7.7 6zm6.16 6.28c-1-.22-1.85-.3-1.85-.78s.43-.51 1.06-.51a1.2 1.2 0 0 1 .92.43.48.48 0 0 0 .35.16h1.35a.23.23 0 0 0 .21-.22c0-.71-.86-1.55-2-1.84v-.75a.27.27 0 0 0-.26-.27h-1.27a.27.27 0 0 0-.26.27v.74a2.31 2.31 0 0 0-2 2c0 2.81 4.07 1.85 4.07 2.89 0 .48-.47.53-1.27.53a1.3 1.3 0 0 1-1-.52.66.66 0 0 0-.4-.17h-1.28a.23.23 0 0 0-.2.22c0 1 1 1.72 2.08 2v.74a.27.27 0 0 0 .26.27h1.25a.26.26 0 0 0 .26-.27v-.71A2.18 2.18 0 0 0 16 14.43c0-1.2-.86-1.88-2.14-2.15z"
                          ></path>
                        </svg>
                        <span>Price to view</span>
                      </div>
                      <div class="price-to-view-side">
                        <span>${{ messagePrice }}</span>
                        <svg viewBox="0 0 24 24" @click="clearMessagePrice">
                          <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="swiper-slider" v-if="sortableMedias.length > 0">
                      <div v-if="isDragListVisible" >
                        <draggable class="sort-change-div" v-model="sortableMedias" :group="'column.components'" handle=".handle" ghost-class="ghost">
                          <div v-for="(element, index) in sortableMedias" :key="index" class="drag-element">
                            <div class="img-wrapper">
                              <img v-if="element.type.indexOf('image/') > -1" :src="element.src" alt="" />
                              <video v-if="element.type.indexOf('video/') > -1">
                                <source :src="element.src" type="video/mp4" />
                              </video>
                              <div class="audio" v-if="element.type.indexOf('audio/') > -1"><span>{{ element.file.name }}</span></div>
                              <span v-if="!element.selected"  class="unchecked-circle" @click="onSelectSortableMedia(index, true)"></span>
                              <span v-if="element.selected" class="checked-circle" @click="onSelectSortableMedia(index, false)">{{element.order}}</span>
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
                            <button :disabled="!applyBtnEnabled" class="btn arrows-btn" @click="applyMediasSort">
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
                      <swiper ref="mySwiper" :options="swiperOptions" :key="sortableMedias.length">
                        <swiper-slide class="slide">
                          <div v-if="!isDragListVisible">
                            <div class="swiper-image-wrapper" v-for="(media, index) in sortableMedias" :key="index">
                              <img v-preview:scope-a class="swiper-lazy" v-if="media.type.indexOf('image/') > -1" :src="media.src" />
                              <video v-preview:scope-a class="swiper-lazy" v-if="media.type.indexOf('video/') > -1">
                                <source :src="media.src" type="video/mp4" />
                              </video>
                              <audio v-preview:scope-a class="swiper-lazy" controls v-if="media.type.indexOf('audio/') > -1">
                                <source :src="media.src" type="audio/mpeg" />
                              </audio>
                              <div class="icon-close" @click="removeSortableMedia(index)">
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
                    <div class="scheduled-message-head" v-if="scheduledMessageDate">
                      <div>
                        <svg class="icon-schedule" viewBox="0 0 24 24">
                          <path d="M19 3h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V5a2 2 0 0 0-2-2zm0 15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9h14zm0-11H5V5h14zM9.79 17.21a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .29-.71 1 1 0 0 0-1-1 1 1 0 0 0-.71.29l-4.29 4.3-1.29-1.3a1 1 0 0 0-.71-.29 1 1 0 0 0-1 1 1 1 0 0 0 .29.71z"></path>
                        </svg> 
                        <span> Scheduled for&nbsp;</span>
                        <strong>{{ moment(scheduledMessageDate).format('MMM DD, h:mm a') }}</strong>
                      </div>
                      <button class="btn close-btn" @click="clearSchedule">
                        <svg class="icon-close" viewBox="0 0 24 24">
                          <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
                        </svg>
                      </button>
                    </div>
                    <div class="multiline-textbox">
                      <textarea
                        placeholder="Type a message"
                        name="text"
                        rows="1"
                        ref="new_message_text"
                        maxlength="10000"
                        spellcheck="false"
                        v-model="newMessageText"
                        @keydown="onCheckReturnKey"
                        @input="onInputNewMessage"
                      ></textarea>
                    </div>
                    <div class="action-btns">
                      <div>
                        <!-- image -->
                        <input
                          type="file"
                          id="image-upload-btn"
                          @change="onMediaChanged"
                          ref="mediaUpload"
                          multiple
                        />
                        <label for="image-upload-btn" class="btn action-btn">
                          <svg id="icon-media" viewBox="0 0 24 24">
                            <path d="M18,3H6A3,3,0,0,0,3,6V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V6A3,3,0,0,0,18,3Zm1,15a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H18a1,1,0,0,1,1,1ZM9,10.5A1.5,1.5,0,1,0,7.5,9,1.5,1.5,0,0,0,9,10.5ZM14,13l-3,3L9,14,6.85,16.15a.47.47,0,0,0-.14.35.5.5,0,0,0,.5.5h9.58a.5.5,0,0,0,.5-.5.47.47,0,0,0-.14-.35Z" fill="#8a96a3"></path>
                          </svg>
                        </label>
                        <!-- video -->
                        <button class="btn action-btn" @click="openVideoRec">
                          <svg id="icon-video" viewBox="0 0 24 24">
                            <path
                              d="M21.79 6a1.21 1.21 0 0 0-.86.35L19 8.25V7a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-1.25l1.93 1.93a1.22 1.22 0 0 0 2.07-.86V7.18A1.21 1.21 0 0 0 21.79 6zM17 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1zm4-2.08l-1.34-1.34a2.25 2.25 0 0 1 0-3.16L21 9.08z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- microphone -->
                        <button class="btn action-btn" @click="showAudioRec = true">
                          <svg id="icon-voice" viewBox="0 0 24 24">
                            <path
                              d="M12 15a4 4 0 0 0 4-4V6a4 4 0 0 0-8 0v5a4 4 0 0 0 4 4zm-2-9a2 2 0 0 1 4 0v5a2 2 0 0 1-4 0zm9 4a1 1 0 0 0-1 1 6 6 0 0 1-12 0 1 1 0 0 0-2 0 8 8 0 0 0 7 7.93V21a1 1 0 0 0 2 0v-2.07A8 8 0 0 0 20 11a1 1 0 0 0-1-1z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- Medis from vault -->
                        <button class="btn action-btn" type="button" @click="openVaultModal">
                          <svg id="icon-vault" viewBox="0 0 24 24">
                            <path
                              d="M20.33,5.69h0l-.9-1.35A3,3,0,0,0,16.93,3H7.07a3,3,0,0,0-2.5,1.34l-.9,1.35A4,4,0,0,0,3,7.91V18a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.91A4,4,0,0,0,20.33,5.69ZM6.24,5.45A1,1,0,0,1,7.07,5h9.86a1,1,0,0,1,.83.45l.37.55H5.87ZM19,18a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V8H19ZM9.5,12.75A1.25,1.25,0,1,0,8.25,11.5,1.25,1.25,0,0,0,9.5,12.75ZM7.93,17h8.14a.42.42,0,0,0,.3-.73L13.7,13.6l-2.55,2.55-1.7-1.7L7.63,16.27a.42.42,0,0,0,.3.73Z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- Schedule -->
                        <button
                          class="btn action-btn"
                          type="button"
                          @click="openScheduleMessageModal"
                        >
                          <svg class="icon-schedule" viewBox="0 0 24 24">
                            <path d="M19 3h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V5a2 2 0 0 0-2-2zm0 15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9h14zm0-11H5V5h14zM9.79 17.21a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .29-.71 1 1 0 0 0-1-1 1 1 0 0 0-.71.29l-4.29 4.3-1.29-1.3a1 1 0 0 0-.71-.29 1 1 0 0 0-1 1 1 1 0 0 0 .29.71z" fill="#8a96a3"></path>
                          </svg>
                        </button>
                        <!-- message price -->
                        <button class="btn action-btn" :disabled="messagePrice" type="button" @click="openMessagePriceModal">
                          <svg id="icon-price" viewBox="0 0 24 24">
                            <path
                              d="M22 13a3.38 3.38 0 0 0-1-2.4l-7.41-7.43A4.06 4.06 0 0 0 10.76 2H5a3 3 0 0 0-3 3v5.76a4 4 0 0 0 1.17 2.83L10.6 21a3.4 3.4 0 0 0 4.8 0l5.6-5.6a3.38 3.38 0 0 0 1-2.4zm-2.4 1L14 19.6a1.45 1.45 0 0 1-2 0l-7.41-7.43A2 2 0 0 1 4 10.76V5a1 1 0 0 1 1-1h5.76a2 2 0 0 1 1.41.59L19.6 12a1.4 1.4 0 0 1 0 2zM7.7 6a1.7 1.7 0 1 0 1.7 1.7A1.7 1.7 0 0 0 7.7 6zm6.16 6.28c-1-.22-1.85-.3-1.85-.78s.43-.51 1.06-.51a1.2 1.2 0 0 1 .92.43.48.48 0 0 0 .35.16h1.35a.23.23 0 0 0 .21-.22c0-.71-.86-1.55-2-1.84v-.75a.27.27 0 0 0-.26-.27h-1.27a.27.27 0 0 0-.26.27v.74a2.31 2.31 0 0 0-2 2c0 2.81 4.07 1.85 4.07 2.89 0 .48-.47.53-1.27.53a1.3 1.3 0 0 1-1-.52.66.66 0 0 0-.4-.17h-1.28a.23.23 0 0 0-.2.22c0 1 1 1.72 2.08 2v.74a.27.27 0 0 0 .26.27h1.25a.26.26 0 0 0 .26-.27v-.71A2.18 2.18 0 0 0 16 14.43c0-1.2-.86-1.88-2.14-2.15z"
                              fill="#8a96a3"></path>
                          </svg>
                        </button>
                      </div>
                      <button class="send-btn btn" :disabled="!(hasNewMessage || sortableMedias.length > 0) || selectedUsers.length < 1" type="button" @click="sendMessage">
                        <b-spinner v-if="isSendingFiles" small></b-spinner>
                        Send
                      </button>
                    </div>
                  </div>
                  <div class="conversation-footer audio-recorder" v-if="showAudioRec">
                    <div class="audio-recorder-header">
                      <div class="d-flex align-items-center">
                        <svg class="icon-voice" viewBox="0 0 24 24">
                          <path d="M12 15a4 4 0 0 0 4-4V6a4 4 0 0 0-8 0v5a4 4 0 0 0 4 4zm-2-9a2 2 0 0 1 4 0v5a2 2 0 0 1-4 0zm9 4a1 1 0 0 0-1 1 6 6 0 0 1-12 0 1 1 0 0 0-2 0 8 8 0 0 0 7 7.93V21a1 1 0 0 0 2 0v-2.07A8 8 0 0 0 20 11a1 1 0 0 0-1-1z"></path>
                        </svg>
                        RECORDING AUDIO
                      </div>
                      <button class="btn icon-close" @click="hideAudioRec">
                        <svg viewBox="0 0 24 24">
                          <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
                        </svg>
                      </button>
                    </div>
                    <div class="audio-recorder-content">
                      <div class="duration">
                        <span>{{ (audioRecDuration - audioRecDuration % 60) / 60 >= 10 ? (audioRecDuration - audioRecDuration % 60) / 60 : '0' + (audioRecDuration - audioRecDuration % 60) / 60 }}:</span>
                        <span>{{ audioRecDuration % 60 >= 10 ? audioRecDuration % 60 : '0' + audioRecDuration % 60 }}</span>
                      </div>
                      <div class="record-btn" @click="toggleAudioRec">
                        <vue-record-audio mode="press" @result="onGetAudioRec" />
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
          <button class="link-btn" @click="clearSchedule">Cancel</button>
          <button
            class="link-btn"
            @click="applySchedule"
            :disabled="!scheduledMessage.date || !scheduledMessage.time || !scheduledMessage.timeState"
          >Apply</button>
        </div>
      </div>
    </b-modal>
    <b-modal hide-header centered hide-footer ref="message-price-modal" title="Message Price Modal">
      <div class="block-modal message-price-modal">
        <h4>MESSAGE PRICE</h4>
        <div class="content mb-3 mt-3">
          <div class="currency-form">
            <span class="prefix">$</span>
            <b-form-input v-model="tempMessagePrice" min="5" type="number" placeholder="5" @change="onMessagePriceChange"></b-form-input>
          </div>
          <span class="extra-info">Minimum $5 USD</span>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeMessagePriceModal">Cancel</button>
          <button class="link-btn" @click="saveMessagePrice">Save</button>
        </div>
      </div>
    </b-modal>
    <b-modal modal-class="unsend_message_modal" hide-header centered hide-footer ref="unsend_message_modal" title="Unsend Message Modal">
      <div class="block-modal">
        <h4>Unsend this message</h4>
        <div class="content mb-3 mt-3">
          Are you sure you want to unsend this message?
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeUnsendMessageModal">Cancel</button>
          <button class="link-btn" @click="unsendTipMessage">Yes, unsend</button>
        </div>
      </div>
    </b-modal>
    <b-modal modal-class="unsend_message_modal" hide-header centered hide-footer ref="confirm_message-price-modal" title="Message Price Modal">
      <div class="block-modal">
        <h4>Message price</h4>
        <div class="content mb-3 mt-3">
          Please confirm you want to purchase a message for ${{ confirm_message_price }}.
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeMessagePriceConfirmModal">Close</button>
          <button class="link-btn" @click="closeMessagePriceConfirmModal">Pay</button>
        </div>
      </div>
    </b-modal>
    <b-modal hide-header centered hide-footer ref="vault-modal" title="Vault Modal">
      <div class="block-modal vault-modal">
        <h4>Vault</h4>
        <div class="content">
          <div class="vault-tags">
            <button
              class="btn tag"
              :class="selectedVaultFilter === filterOption ? 'selected': ''"
              @click="filterVaultFiles(filterOption)"
              v-for="filterOption in vaultFilterOptions"
              :key="filterOption"
            >
              <svg class="icon-done" viewBox="0 0 24 24">
                <path d="M9 19.42l-5.71-5.71A1 1 0 0 1 3 13a1 1 0 0 1 1-1 1 1 0 0 1 .71.29L9 16.59l10.29-10.3A1 1 0 0 1 20 6a1 1 0 0 1 1 1 1 1 0 0 1-.29.71z"></path>
              </svg>
              <span>{{ filterOption }}</span>
            </button>
          </div>
          <div class="text-center" v-if="isVaultLoading">
            <b-spinner variant="secondary" label="Loading..." small></b-spinner>
          </div>
          <div class="gallery-list" v-if="!isVaultLoading && !vaultFiles.length">
            <p class="empty">Nothing was found</p>
          </div>
          <div class="gallery-list" v-if="!isVaultLoading && vaultFiles.length">
            <div class="img-wrapper" v-for="media in vaultFiles" :key="media.id">
              <img v-preview:scope-a v-if="media.is_image" :src="media.filepath" :alt="media.mfname" />
              <video v-if="media.is_video" @click="() => showMediaPopup(media)">
                <source :src="media.filepath" type="video/mp4" />
              </video>
              <svg v-if="media.is_video" class="video-play-svg" viewBox="0 0 142.448 142.448" style="enable-background:new 0 0 142.448 142.448;">
                <g>
                  <path d="M142.411,68.9C141.216,31.48,110.968,1.233,73.549,0.038c-20.361-0.646-39.41,7.104-53.488,21.639
                    C6.527,35.65-0.584,54.071,0.038,73.549c1.194,37.419,31.442,67.667,68.861,68.861c0.779,0.025,1.551,0.037,2.325,0.037
                    c19.454,0,37.624-7.698,51.163-21.676C135.921,106.799,143.033,88.377,142.411,68.9z M111.613,110.336
                    c-10.688,11.035-25.032,17.112-40.389,17.112c-0.614,0-1.228-0.01-1.847-0.029c-29.532-0.943-53.404-24.815-54.348-54.348
                    c-0.491-15.382,5.122-29.928,15.806-40.958c10.688-11.035,25.032-17.112,40.389-17.112c0.614,0,1.228,0.01,1.847,0.029
                    c29.532,0.943,53.404,24.815,54.348,54.348C127.91,84.76,122.296,99.306,111.613,110.336z"/>
                  <path d="M94.585,67.086L63.001,44.44c-3.369-2.416-8.059-0.008-8.059,4.138v45.293
                    c0,4.146,4.69,6.554,8.059,4.138l31.583-22.647C97.418,73.331,97.418,69.118,94.585,67.086z"/>
                </g>
              </svg>
              <img v-if="media.mimetype.indexOf('audio/') > -1" src="/images/audio-thumb.png" alt="" @click="showMediaPopup(media)" />
              <span class="timestamp">{{ moment(media.created_at).format('MMM DD') }}</span>
              <div class="checkbox" @click="selectVaultFiles(media)">
                <round-check-box
                  :value="filesFromVault.findIndex(m => m.id === media.id) > -1"
                  :key="filesFromVault.findIndex(m => m.id === media.id) > -1"
                />
              </div>
            </div>
          </div>
        </div>
        <div class="action-btns">
          <button class="link-btn" @click="closeVaultModal">Cancel</button>
          <button class="link-btn" @click="addVaultFilestoMedias" :disabled="!filesFromVault.length">Add</button>
        </div>
      </div>
    </b-modal>
    <b-modal modal-class="media_modal" hide-header centered hide-footer ref="media_modal" title="Video/Audio Popup">
      <div class="video-modal" v-if="popupMedia && popupMedia.is_video">
        <video controls autoplay>
          <source :src="popupMedia.filepath" type="video/mp4" />
        </video>
      </div>
      <div class="audio-modal" v-if="popupMedia && popupMedia.mimetype.indexOf('audio/') > -1">
        <audio controls autoplay>
          <source :src="popupMedia.filepath" type="audio/mpeg" />
        </audio>
      </div>
    </b-modal>
    <div :class="showVideoRec ? '' : 'd-none'" class="video-rec-wrapper">
      <h4>Record Video Message</h4>
      <video id="myVideo" playsinline class="video-js vjs-default-skin"></video>
      <div class="icon-close" @click="hideVideoRec">
        <svg viewBox="0 0 24 24">
          <path d="M13.41 12l5.3-5.29A1 1 0 0 0 19 6a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L12 10.59l-5.29-5.3A1 1 0 0 0 6 5a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l5.3 5.29-5.3 5.29A1 1 0 0 0 5 18a1 1 0 0 0 1 1 1 1 0 0 0 .71-.29l5.29-5.3 5.29 5.3A1 1 0 0 0 18 19a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71z"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script>
  import _ from 'lodash';
  import moment from 'moment';
  import { Swiper, SwiperSlide, directive } from 'vue-awesome-swiper';
  import PhotoSwipe from 'photoswipe/dist/photoswipe';
  import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default';
  import createPreviewDirective from 'vue-photoswipe-directive';
  import draggable from 'vuedraggable';

  import videojs from 'video.js';
  import RecordRTC from 'recordrtc';
  import Record from 'videojs-record/dist/videojs.record.js';
  import TsEBMLEngine from 'videojs-record/dist/plugins/videojs.record.ts-ebml.js';

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
      newMessageText: undefined,
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
      messagePrice: undefined,
      isSendingFiles: false,
      hasNewMessage: false,
      tempMessagePrice: undefined,
      confirm_message_price: undefined,
      showVideoRec: false,
      showAudioRec: false,
      audioRecDuration: 0,
      audioRecInterval: undefined,
      swiperOptions: {
        lazy: true,
        slidesPerView: 'auto',
        observer: true,
        observeParents: true,
      },
      isDragListVisible: false,
      sortableMedias: [],
      applyBtnEnabled: false,
      filesFromVault: [],
      popupMedia: undefined,
      vaultFiles: [],
      isVaultLoading: false,
      selectedVaultFilter: undefined,
      vaultFilterOptions: [
        'stories',
        'posts',
        'messages',
        'vaultfolders',
      ],
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
      Swiper,
      SwiperSlide,
      draggable,
      'round-check-box': RoundCheckBox,
      'radio-group-box': RadioGroupBox,
      'counter': Counter,
    },
    directives: {
      swiper: directive,
      preview: createPreviewDirective({
          showAnimationDuration: 0,
          bgOpacity: 0.75
        }, PhotoSwipe, PhotoSwipeUI)
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
          let promise;
          // Sending media files
          if (this.sortableMedias.length > 0) {
            this.isSendingFiles = true;
            // const mediafilesLinks = [];
            const data = new FormData();
            this.sortableMedias.map((media, index) => {
              const { file, mftype, src } = media;
              if (mftype !== 'vault') {
                data.append('mediafile[]', file);
                data.append('vaultfiles[]', null);
              } else {
                data.append('mediafile[]', null);
                data.append('vaultfiles[]', file);
              }
            });
            data.append('user_id', user.id);
            if (this.newMessageText) {
              data.append('message', this.newMessageText);
            }
            if (this.messagePrice) {
              data.append('tip_price', this.messagePrice);
            }
            promise = this.axios.post('/chat-messages', data);
          } else if (this.newMessageText) {
            promise = this.axios.post('/chat-messages', {
              message: this.newMessageText,
              tip_price: this.messagePrice,
              user_id: user.id,
            });
          }
          promises.push(promise);
        })
   
        Promise.all(promises).then(function() {
          self.isSendingFiles = false;
          self.newMessageText = undefined;
          self.adjustTextareaSize();
          self.sortableMedias = [];
          self.messagePrice = undefined;
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
      openScheduleMessageModal: function() {
        this.$refs['schedule-message-modal'].show();
      },
      applySchedule: function() {
        this.scheduledMessageDate = moment(`${this.scheduledMessage.date} ${this.scheduledMessage.time}`).unix() * 1000;
        this.$refs['schedule-message-modal'].hide();
        this.scheduledMessage = {};
      },
      clearSchedule: function() {
        this.scheduledMessageDate = undefined;
        this.scheduledMessage = {};
        this.$refs['schedule-message-modal'].hide();
      },
      addNewMedia: function() {
        this.$refs.mediaUpload.click();
      },
      removeSortableMedia: function(index) {
        const newArr = this.sortableMedias.slice();
        newArr.splice(index, 1);
        
        this.sortableMedias = newArr;
        if (this.$refs.mySwiper) {
          this.$refs.mySwiper.updateSwiper();
        }
      },
      onSelectSortableMedia: function(index, status) {
        const newArr = this.sortableMedias.slice();
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
        this.sortableMedias = newArr;
        this.applyBtnEnabled = true;
      },
      applyMediasSort: function() {
        const newArr = this.sortableMedias.slice();
        const sortedArr = _.orderBy(newArr, ['order'], ['asc']);
        sortedArr.forEach(item => {
          item.order = undefined;
          item.selected = undefined;
        });
        this.sortableMedias = sortedArr;
        this.applyBtnEnabled = false;
      },
      confirmImgsSort: function() {
        this.applyMediasSort();
        this.isDragListVisible = false;
      },
      onMediaChanged: function(e) {
        const files = _.values(e.target.files);
        files.forEach(file => {
          console.log('---- file.type:', file.type);
          this.sortableMedias.push({
            src: URL.createObjectURL(file),
            file,
            type: file.type,
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
      openMessagePriceModal: function() {
        this.tempMessagePrice = undefined;
        this.$refs['message-price-modal'].show();
      },
      closeMessagePriceModal: function() {
        this.tempMessagePrice = undefined;
        this.$refs['message-price-modal'].hide();
      },
      saveMessagePrice: function() {
        this.messagePrice = this.tempMessagePrice;
        this.$refs['message-price-modal'].hide();
        console.log('messagePrice:', this.messagePrice);
      },
      onMessagePriceChange: function(val) {
        if (val < 5) {
          this.tempMessagePrice = 5;
        } else {
          this.tempMessagePrice = val;
        }
      },
      clearMessagePrice: function() {
        this.messagePrice = undefined;
      },
      openUnsendMessageModal: function(messageId) {
        this.$refs['unsend_message_modal'].show();
        this.unsendTipMessageId = messageId;
      },
      closeUnsendMessageModal: function() {
        this.unsendTipMessageId = undefined;
        this.$refs['unsend_message_modal'].hide();
      },
      unsendTipMessage: function() {
        this.axios.delete(`/chat-messages/${this.$route.params.id}/threads/${this.unsendTipMessageId}`)
          .then(() => {
            this.closeUnsendMessageModal();
            const newMessages = [...this.messages];
            const idx = newMessages.findIndex(message => message.id === this.unsendTipMessageId);
            newMessages.splice(idx, 1);
            this.messages = newMessages;
          });
        this.unsendTipMessageId = undefined;
      },
      openMessagePriceConfirmModal: function(value) {
        this.confirm_message_price = value;
        this.$refs['confirm_message-price-modal'].show();
      },
      closeMessagePriceConfirmModal: function() {
        this.$refs['confirm_message-price-modal'].hide();
      },
      onCheckReturnKey: function(e) {
        if (e.ctrlKey && e.keyCode == 13) {
          this.sendMessage();
        }
      },
      clearMessages: function (receiver) {
        this.axios.delete(`/chat-messages/${receiver.id}`)
          .then(() => {
            const idx = this.users.findIndex(user => user.profile.id === receiver.id);
            this.users.splice(idx, 1);
            this.$router.push('/messages');
          })
      },
      setLikeMessage: function(message) {
        this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/like`)
          .then(() => {
            const newMessages = [...this.messages];
            newMessages.forEach(thread => {
              const idx = thread.messages.findIndex(m => m.id === message.id);
              if (idx > -1) {
                thread.messages[idx].is_like = 1;
              }
            });
            this.messages = newMessages;
          })
      },
      setUnlikeMessage: function(message) {
        this.axios.post(`/chat-messages/${this.$route.params.id}/threads/${message.id}/unlike`)
          .then(() => {
            const newMessages = [...this.messages];
            newMessages.forEach(thread => {
              const idx = thread.messages.findIndex(m => m.id === message.id);
              if (idx > -1) {
                thread.messages[idx].is_like = 0;
              }
            });
            this.messages = newMessages;
          })
      },
      openVideoRec: function() {
        this.showVideoRec = true;
        const options = {
          controls: true,
          fluid: true,
          bigPlayButton: true,
          controlBar: {
              volumePanel: true
          },
          plugins: {
              record: {
                  audio: false,
                  video: true,
                  maxLength: 10,
                  displayMilliseconds: true,
                  debug: true,
                  convertEngine: 'ts-ebml'
              }
          }
        };
        const player = videojs('myVideo', options, function() {
        });
        const self = this;
        player.on('finishRecord', function() {
          console.log('finished recording: ', player.recordedData);
          self.sortableMedias.push({
            src: URL.createObjectURL(player.recordedData),
            file: player.recordedData,
            type: 'video/mp4',
          });
        });
      },
      hideVideoRec: function() {
        this.showVideoRec = false;
      },
      onGetAudioRec: function(data) {
        const self = this;
        this.sortableMedias.push({
          src: URL.createObjectURL(data),
          file: data,
          type: 'audio/mp3',
        });
        setTimeout(() => {
          self.hideAudioRec();
        }, 1000);
      },
      hideAudioRec: function() {
        this.showAudioRec = false;
        this.audioRecDuration = 0;
        clearInterval(this.audioRecInterval);
        this.audioRecInterval = undefined;
      },
      toggleAudioRec: function() {
        const self = this;
        if (!this.audioRecInterval) {
          this.audioRecInterval = setInterval(function() {
            self.audioRecDuration += 1;
          }, 1000);
        } else {
          setTimeout(() => {
            self.hideAudioRec();
          }, 1000);
        }
      },
      openVaultModal: function() {
        this.$refs['vault-modal'].show();
        this.isVaultLoading = true;
        this.axios.get('/vaults/all-files')
          .then(response => {
            this.vaultFiles = response.data.mediafiles;
            this.isVaultLoading = false;
          })
      },
      closeVaultModal: function() {
        this.filesFromVault = [];
        this.$refs['vault-modal'].hide();
      },
      showMediaPopup: function(media) {
        this.popupMedia = media;
        this.$refs['media_modal'].show();
      },
      closeMediaPopup: function() {
        this.popupMedia = undefined;
        this.$refs['media_modal'].hide();
      },
      addVaultFilestoMedias: function() {
        const self = this;
        this.filesFromVault.forEach(file => {
          self.sortableMedias.push({
            src: file.filepath,
            file: file.id,
            type: file.mimetype,
            mftype: 'vault',
          });
        });
        this.closeVaultModal();
      },
      selectVaultFiles: function(mediafile) {
        const idx = this.filesFromVault.findIndex(m => m.id === mediafile.id);
        if (idx < 0) {
          this.filesFromVault.push(mediafile);
        } else {
          this.filesFromVault.splice(idx, 1);
        }
      },
      filterVaultFiles: function(filterOption) {
        if (this.selectedVaultFilter === filterOption) {
          this.selectedVaultFilter = undefined;
          this.isVaultLoading = true;
          this.axios.get(`/vaults/all-files`)
            .then(response => {
              this.vaultFiles = response.data.mediafiles;
              this.isVaultLoading = false;
            })
        } else {
          this.selectedVaultFilter = filterOption;
          this.isVaultLoading = true;
          this.axios.get(`/vaults/all-files?query=${filterOption}`)
            .then(response => {
              this.vaultFiles = response.data.mediafiles;
              this.isVaultLoading = false;
            })
        }
      },
      onInputNewMessage: function(e) {
        this.newMessageText = e.target.value;
        this.adjustTextareaSize();
        if (this.newMessageText) {
          this.hasNewMessage = true;
        } else {
          this.hasNewMessage = false;
        }
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
      adjustTextareaSize: function() {
        const limit = 100;
        const textarea = this.$refs.new_message_text;
        if (textarea) {
          textarea.style.height = '1px';
          textarea.style.height = Math.min(textarea.scrollHeight, limit) + "px";
        }
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