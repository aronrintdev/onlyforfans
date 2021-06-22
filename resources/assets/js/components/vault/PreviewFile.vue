<template>
  <div>
    <div class="img-wrap position-relative">
      <img class="OFF-img-fluid" :src="mediafile.filepath" />
      <b-form-checkbox position-absolute ref="checkbox" size="lg" :checked="mediafile.selected" :value="true" @change="onSelect" />
    </div>
    <span>{{ mediafile.orig_filename }}</span>
  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'

export default {
  model: {
    prop: 'mediafile',
  },

  props: {
    mediafile: { type: Object, default: () => ({ mediafile: {} })},
    //selected: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      return !this.session_user || !this.mediafile
    },

  },

  data: () => ({
    moment: moment,
  }), // data

  created() { },

  mounted() { },

  methods: {
    onClicked() {
console.log('onClicked')
      this.onSelect(!this.mediafile.selected)
    },

    onSelect(value) {
console.log('onSelect')
      if (value) {
        this.$emit('input', { ...this.mediafile, selected: true })
      } else {
        this.$emit('input', { ...this.mediafile, selected: false })
      }
    },
  }, // methods


  watch: { }, // watch

  components: { },

}
</script>

<style lang="scss" scoped>
body {
  .img-wrap {
    padding-top: calc(25% - 2px);
    img {
      object-fit: cover;
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
  }
}
</style>
