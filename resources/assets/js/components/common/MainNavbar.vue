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

    <ScrollCollapse v-if="mobile" class="w-100">
      <SearchBar class="w-100 mt-3" />
      <NavButtons :mobile-style="mobile" class="w-100 mt-3" />
    </ScrollCollapse>

    <b-navbar-nav v-if="!mobile">
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
    ...Vuex.mapGetters(['session_user']),
    mobileWidth() {
      if (typeof this.toggleMobileAt === 'number') {
        return this.toggleMobileAt
      }
      return parseInt(getComputedStyle(document.documentElement)
        .getPropertyValue(`--breakpoint-${this.toggleMobileAt}`).replace('px', ''))
    },
  },

  data: () => ({
    mobile: false,
    screenWidth: null,
  }),

  methods: {
  },

  watch: {
    $vssWidth(value) {
      if (value < this.mobileWidth) {
        this.mobile = true
      } else {
        this.mobile = false
      }
    },
  },

  mounted() {
    if (this.$vssWidth < this.mobileWidth) {
      this.mobile = true
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
</style>
