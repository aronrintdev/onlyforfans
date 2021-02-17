/**
 * Luxon DateTime mixin
 */

import Vue from 'vue'
import { DateTime } from 'luxon'

Vue.mixin({ methods: { $DateTime: (options = {}) => ( new DateTime(options)) }})
