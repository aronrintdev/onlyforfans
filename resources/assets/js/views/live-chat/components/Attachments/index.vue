<template>
  <div v-if="attachments" class="attachments d-block">
    <Item v-for="item in nonMedia" :key="item.id" :value="item" />
    <div v-if="media.length > 0" class="position-relative">
      <b-badge variant="primary" pill class="h4 image-count">
        <fa-icon icon="images" />
        {{ media.length }}
      </b-badge>
      <MediaSlider  :mediafiles="media" />
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Attachments/index.vue
 */
import _ from 'lodash'
import Vuex from 'vuex'
import Item from './Item'
import MediaSlider from '@components/media/PreviewSlider'

export default {
  name: 'Attachments',

  components: {
    Item,
    MediaSlider,
  },

  props: {
    attachments: { type: Array, default: () => ([])},
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    /** Attachments that are considered media i.e. images, video */
    media() {
      return _.filter(this.attachments, o => (o.filepath))
    },
    /** Attachments that are considered non media i.e. tips */
    nonMedia() {
      return _.filter(this.attachments, o => (!o.filepath))
    },
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.image-count {
  position: absolute;
  top: 0;
  left: 0;
  font-size: 100%;
  z-index: 2;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
