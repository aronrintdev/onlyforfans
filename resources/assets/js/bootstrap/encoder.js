/**
 * js/bootstrap/encoder.js
 */

import Vue from 'vue'
import UuidEncoder from 'uuid-encoder'

/**
 * uuid-encoder
 * https://www.npmjs.com/package/uuid-encoder
 *
 * string encode(string uuid)
 * string decode(string str)
 */
const encoder = new UuidEncoder('base64url')

Vue.mixin({
  computed: {
    $encoder: () => encoder,
  },
  methods: {
    $newEncoder: encoding => new UuidEncoder(encoding),
    $checkUuid: value => (
      /^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i
        .test(value)
    )
  },
})
