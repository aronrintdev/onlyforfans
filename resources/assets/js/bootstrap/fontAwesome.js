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
library.add(fas, far, fab)

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

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.component('font-awesome-layers', FontAwesomeLayers)
Vue.component('font-awesome-layers-text', FontAwesomeLayersText)
// Alias
Vue.component('fa-icon', FontAwesomeIcon)
Vue.component('fa-layers', FontAwesomeLayers)
Vue.component('fa-layers-text', FontAwesomeLayersText)
