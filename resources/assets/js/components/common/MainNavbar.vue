<template>
  <b-navbar v-if="navbarVisible" :toggleable="mobile" variant="light" class="bg-white" :class="{ 'pb-0': searchVisible }" >
    <b-navbar-brand :to="{ name: 'index' }" class="navbar-brand" :class="mobile ? 'mr-2' : 'mr-5'">
      <Branding type="text" size="lg" variant="brand" />
    </b-navbar-brand>
    <div class="ml-auto mr-3" @click="toggleSearchBar">
      <fa-icon v-if="mobile" icon="search" class="text-secondary" />
    </div>

    <ScrollCollapse v-if="mobile && searchVisible" ref="scrollCollapse" class="scrollCollapse w-100" :full-open="searchOpen" :full-open-height="0">
      <div class="d-flex flex-column justify-content-between pb-3">
        <SearchBar class="w-100 mt-3" :mobile="true" @opening="searchOpen = true" @closing="searchOpen = false" @scroll="onScroll" />
      </div>
    </ScrollCollapse>

    <!-- <NavButtons v-if="mobile" :mobile-style="mobile" :unread-messages-count="unread_messages_count" class="w-100 mt-3" /> -->

    <b-navbar-nav v-if="!mobile" class="search-nav">
      <SearchBar />
    </b-navbar-nav>
    <b-navbar-nav v-if="!mobile" class="ml-auto">
      <NavButtons class="mx-3" :unread-notifications-count="unread_notifications_count" :unread-messages-count="unread_messages_count" />
      <b-nav-item-dropdown
        id="profile-dropdown"
        toggle-class="nav-link-custom"
        right
        class="py-0"
        no-caret
      >
        <template #button-content>
          <ProfileButton />
        </template>
        <ProfileMenu v-if="session_user" dropdown class="meta-username" :data-username="session_user.username"/>
      </b-nav-item-dropdown>
    </b-navbar-nav>
  </b-navbar>
</template>

<script>
import Vuex from 'vuex'
import Branding from '@components/common/Branding'
import NavButtons from './navbar/NavButtons'
import ProfileButton from './navbar/ProfileButton'
import ProfileMenu from './navbar/ProfileMenu'
import SearchBar from './navbar/SearchBar'
import VueScreenSize from 'vue-screen-size'
import ScrollCollapse from '@components/common/ScrollCollapse'

export default {
  components: {
    Branding,
    NavButtons,
    ProfileButton,
    ProfileMenu,
    SearchBar,
    ScrollCollapse,
  },

  mixins: [VueScreenSize.VueScreenSizeMixin],

  props: {
    toggleMobileAt: { type: [String, Number], default: 'md', },
    unreadMessagesCount: { type: Number, default: 0 },
    //unreadNotificationsCount: { type: Number, default: 0 },
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapGetters(['session_user', 'unread_messages_count', 'unread_notifications_count']),

    openHeight() {
      var height = this.$vssHeight
      if (this.$el) {
        height = height - this.$el.clientHeight - 68
      }

      if(this.$refs['scrollCollapse']) {
        return height + this.$refs['scrollCollapse'].maxHeight
      }
      return height
    },

    mobileOpenHeight() {
      this.openHeight
    },

    navbarVisible() {
      if (this.mobile && this.$route.path.startsWith('/messages')) {
        // on mobile, if we're in the messages, hide navbar
        return false
      }

      return true
    }
  },

  data: () => ({
    searchOpen: false,
    screenWidth: null,
    searchVisible: false,
  }),

  methods: {
    onScroll() {
      if (this.searchOpen) {
        this.$forceCompute('openHeight')
      }
    },

    toggleSearchBar() {
      this.searchVisible = !this.searchVisible
    },
  },

  watch: {
    mobile(value) {
      if (!value) {
        this.searchOpen = false
      }
    },
    searchOpen() {
      this.$forceCompute('openHeight')
    }
  },

}
</script>

<style lang="scss" scoped>
.nav-bar {
  position: unset;
}
.navbar-toggler {
  border: none;
  outline: none;
}
.navbar-collapse {
  &.collapse { // When mobile
    .search {
      margin-top: 1rem;
      width: 100%;
      .form-inline {
        width: 100%;
        .form-inline {
          width: 100%;
        }
      }
    }

    .profile {
      .button-content {
        justify-content: flex-end;
      }
    }
  }
}
.scrollCollapse {
  height: auto !important;
}

.scroll-collapse-nav {
  overflow-y: hidden;
  & > div {
    position: relative;
  }
}

.search-nav {
  width: 500px;
  .form-inline {
    width: 100%;
  }
}

</style>
