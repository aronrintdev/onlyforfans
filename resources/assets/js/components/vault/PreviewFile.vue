<template>
  <div>
    <div class="img-box position-relative">
      <img :class=" mediafile.selected ? 'tag-selected' : ''" :src="mediafile.filepath" />
      <div class="render-date">
        <p class="m-0">{{  moment(mediafile.created_at).format('MMMM D') }}</p>
      </div>
      <div class="select-file">
        <b-form-checkbox ref="checkbox" size="lg" :checked="mediafile.selected" :value="true" @change="onSelect" />
      </div>
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
  .img-box {
    padding-top: calc(85% - 2px);
    img {
      object-fit: cover;
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
    img.tag-selected {
      filter: brightness(50%);
    }
    .render-date {
      position: absolute;
      top: 5px;
      left: 5px;
      border-radius: 5px;
      background: #535353;
      opacity: 0.7;
      padding: 0.3rem;
      p {
        opacity: 1;
        color: #fff;
        font-size: 11px;
      }
    }
    .select-file {
      position: absolute;
      top: 0;
      right: 0;
    }
  }
}
</style>
