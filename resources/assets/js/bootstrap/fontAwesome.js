/**
 * Enables Font Awesome Vue components
 */

import Vue from 'vue'

import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText } from '@fortawesome/vue-fontawesome'

library.add(fas)
library.add(far)

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.component('font-awesome-layers', FontAwesomeLayers)
Vue.component('font-awesome-layers-text', FontAwesomeLayersText)
// Alias
Vue.component('fa-icon', FontAwesomeIcon)
Vue.component('fa-layers', FontAwesomeLayers)
Vue.component('fa-layers-text', FontAwesomeLayersText)
