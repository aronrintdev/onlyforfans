<template>
  <b-navbar :toggleable="mobile" variant="light" sticky class="bg-white" :class="{ 'pb-0': mobile }" >
    <b-navbar-brand :to="{ name: 'index' }" class="navbar-brand mr-5">
      All Fans
    </b-navbar-brand>
    <b-navbar-toggle target="nav-collapse" class="mb-2">
      <ProfileButton />
    </b-navbar-toggle>
    <b-collapse v-if="mobile" id="nav-collapse" is-nav>
      <b-navbar-nav>
        <ProfileMenu v-if="session_user" />
      </b-navbar-nav>
    </b-collapse>

    <ScrollCollapse v-if="mobile" ref="scrollCollapse" class="w-100" :full-open="searchOpen" :full-open-height="openHeight">
      <div class="d-flex flex-column justify-content-between h-100">
        <SearchBar class="w-100 mt-3" :mobile="true" @opening="searchOpen = true" @closing="searchOpen = false" @scroll="onScroll" />
        <NavButtons :mobile-style="mobile" class="w-100 mt-3" />
      </div>
    </ScrollCollapse>

    <b-navbar-nav v-if="!mobile" class="search-nav">
      <SearchBar />
    </b-navbar-nav>
    <b-navbar-nav v-if="!mobile" class="ml-auto">
      <NavButtons class="mx-3" />
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
        <ProfileMenu v-if="session_user" dropdown />
      </b-nav-item-dropdown>
    </b-navbar-nav>
  </b-navbar>
</template>

<script>
import Vuex from 'vuex'
import NavButtons from './navbar/NavButtons'
import ProfileButton from './navbar/ProfileButton'
import ProfileMenu from './navbar/ProfileMenu'
import SearchBar from './navbar/SearchBar'
import VueScreenSize from 'vue-screen-size'
import ScrollCollapse from '@components/common/ScrollCollapse'

export default {
  components: {
    NavButtons,
    ProfileButton,
    ProfileMenu,
    SearchBar,
    ScrollCollapse,
  },

  mixins: [VueScreenSize.VueScreenSizeMixin],

  props: {
    toggleMobileAt: { type: [String, Number], default: 'md', },
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapGetters(['session_user']),
    openHeight() {
      const height = this.$vssHeight - this.$el.clientHeight
      if(this.$refs['scrollCollapse']) {
        return height + this.$refs['scrollCollapse'].maxHeight
      }
      return height
    },
    mobileOpenHeight() {
      this.openHeight
    }
  },

  data: () => ({
    searchOpen: false,
    screenWidth: null,
  }),

  methods: {
    onScroll() {
      if (this.searchOpen) {
        this.$forceCompute('openHeight')
      }
    }
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

  mounted() {
    if (this.$vssWidth < this.mobileWidth) {
    }
  }

}
</script>

<style lang="scss" scoped>
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
