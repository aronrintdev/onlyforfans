<template>
  <div class="app d-flex flex-column">
    <!-- Header -->
    <div v-if="isVisible" class="header">
      <b-navbar variant="light" >
        <b-navbar-brand :to="{ name: 'index' }" class="navbar-brand mr-5">
          <Branding type="text" size="lg" class="my-1" :variant="'brand'" />
        </b-navbar-brand>
        <b-btn variant="dark" @click="$router.push({ name: 'login' })" class="ml-auto">{{ $t('login') }}</b-btn>
      </b-navbar>
    </div>
    <div class="content d-flex flex-grow-1" :class="{ 'px-0': mobile, 'py-3': isVisible }">
      <router-view />
    </div>
    <div v-if="isVisible" class="footer">
      <b-navbar variant="dark">
        <b-nav-text class="text-light mx-auto">
          &copy; {{ $DateTime().year }} AllFans. All rights reserved.
        </b-nav-text>
      </b-navbar>
    </div>
  </div>
</template>

<script>
/**
 * View Template for guest pages
 */
import Vuex from 'vuex'
import VueScreenSize from 'vue-screen-size'
import Branding from '@components/common/Branding'

export default {
  name: 'Guest',

  components: {
    Branding,
  },

  mixins: [VueScreenSize.VueScreenSizeMixin],

  props: {
    toggleMobileAt: { type: [String, Number], default: 'md', },
    screenSizesTypes: { type: Object, default: () => ({ xs: 0, sm: 0, md: 0, lg: 0, xl: 0 }) }
  },

  data:() => ({
    isVisible: true,
  }),

  computed: {
    ...Vuex.mapState([ 'mobile', 'screenSize' ]),
    mobileWidth() {
      if (typeof this.toggleMobileAt === 'number') {
        return this.toggleMobileAt
      }
      return parseInt(getComputedStyle(document.documentElement)
        .getPropertyValue(`--breakpoint-${this.toggleMobileAt}`).replace('px', ''))
    },
    screenSizes() {
      return _.mapValues(this.screenSizesTypes ,(value, key) => {
        return parseInt(getComputedStyle(document.documentElement)
          .getPropertyValue(`--breakpoint-${key}`).replace('px', ''))
          || value
      })
    }
  },

  mounted() {
    if(this.$route && (this.$route.name === 'login' || this.$route.name === "register" || this.$route.name === 'error-not-found' || this.$route.name === 'forgot-password')) {
        this.isVisible = false
      }
  },

  methods: {
    ...Vuex.mapMutations([ 'UPDATE_MOBILE', 'UPDATE_SCREEN_SIZE' ]),
  },

  watch: {
    $route(val) {
      if(val && (val.name === 'login' || val.name === "register" || val.name === 'error-not-found' || val.name === 'forgot-password')) {
        this.isVisible = false
      }
    },
    $vssWidth(value) {
      var mobile = value < this.mobileWidth
      if (this.mobile !== mobile) {
        this.UPDATE_MOBILE(mobile)
      }
      var screenSize = _.findKey(this.screenSizes, i => (i === _.max(_.filter(this.screenSizes, size => ( value >= size )))))
      if (screenSize !== this.screenSize) {
        this.UPDATE_SCREEN_SIZE(screenSize)
      }
    },
  },

  created() {
    if (this.$vssWidth < this.mobileWidth) {
      this.UPDATE_MOBILE(true)
    }
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "login": "Login"
  }
}
</i18n>
