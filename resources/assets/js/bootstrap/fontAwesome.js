/**
 * Enables Font Awesome Vue components
 */

import Vue from 'vue'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText } from '@fortawesome/vue-fontawesome'

// Optimized for development
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { fas as fasPro } from '@fortawesome/pro-solid-svg-icons'
import { far as farPro } from '@fortawesome/pro-regular-svg-icons'
import { fal } from '@fortawesome/pro-light-svg-icons'
import { fad } from '@fortawesome/pro-duotone-svg-icons'
library.add(fas, far, fab, fasPro, farPro, fal, fad)

// Optimization for live mode
/*
import {
  faBell, faCaretDown, faCaretLeft, faCaretRight, faCheck, faComments, faCompass, faCreditCard, faDollarSign, faEdit,
  faEllipsisV, faEnvelope, faExternalLinkAlt, faFileInvoiceDollar, faFolderPlus, faHeart, faHome, faLockOpen,
  // faLockOpenAlt, This is a pro Icon
  faPen, faPlus, faRssSquare, faSave, faSearch, faSpinner, faStar, faStream, faSync, faTimes, faTrash, faUndo,
  // faUsdCircle, This is a pro icon
  // faUsdSquare, This is a pro icon
  faUsers, faWalking
} from '@fortawesome/free-solid-svg-icons'
library.add(
  faBell, faCaretDown, faCaretLeft, faCaretRight, faCheck, faComments, faCompass, faCreditCard, faDollarSign, faEdit,
  faEllipsisV, faEnvelope, faExternalLinkAlt, faFileInvoiceDollar, faFolderPlus, faHeart, faHome, faLockOpen,
  // faLockOpenAlt,
  faPen, faPlus, faRssSquare, faSave, faSearch, faSpinner, faStar, faStream, faSync, faTimes, faTrash,  faUndo,
  // faUsdCircle,
  // faUsdSquare,
  faUsers, faWalking
)

import { faStar as farStar } from '@fortawesome/free-regular-svg-icons'
library.add(farStar)

import {
  faBitcoin, faCcAmazonPay, faCcAmex, faCcApplePay, faCcDinersClub, faCcDiscover, faCcJcb, faCcMastercard,
  faCcPaypal, faCcStripe, faCcVisa, faPaypal
} from '@fortawesome/free-brands-svg-icons'
library.add(
  faBitcoin, faCcAmazonPay, faCcAmex, faCcApplePay, faCcDinersClub, faCcDiscover, faCcJcb, faCcMastercard,
  faCcPaypal, faCcStripe, faCcVisa, faPaypal
)
*/

import Vuex from 'vuex'

const IconComponent = {
  props: FontAwesomeIcon.props,
  computed: {
    ...Vuex.mapState([ 'iconStyle' ]),
  },
  methods: {
    ...Vuex.mapMutations([ 'UPDATE_ICON_STYLE' ]),
  },
  render(h) {
    var icon = this.icon
    if (typeof icon === 'string') {
      icon = [ this.iconStyle || 'fas', icon ]
    }
    return h(
      FontAwesomeIcon, {
        props: {
          ...this.$props,
          icon: icon
        },
        attrs: this.$attrs
      }
    )
  }
}

Vue.component('font-awesome-icon', IconComponent)
Vue.component('font-awesome-layers', FontAwesomeLayers)
Vue.component('font-awesome-layers-text', FontAwesomeLayersText)
// Alias
Vue.component('fa-icon', IconComponent)
Vue.component('fa-layers', FontAwesomeLayers)
Vue.component('fa-layers-text', FontAwesomeLayersText)
