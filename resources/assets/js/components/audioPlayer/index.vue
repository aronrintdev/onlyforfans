<template>
  <div class="wrap">
    <vue-plyr>
      <audio controls playsinline>
        <source :src="source.filepath" type="audio/webm" />
        <source :src="source.filepath" type="audio/mp3" />
        <source :src="source.filepath" type="audio/ogg" />
      </audio>
    </vue-plyr>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/audioPlayer/index.vue
 */
import Vuex from 'vuex'

export default {
  name: 'index',

  components: {},

  props: {
    source: { type: Object, default: () => ({})}
  },

  computed: {},

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.wrap {
  width: 100%;
  max-width: 80vw;
  margin: auto;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
.wrap::v-deep .plyr {
  height: auto;
}
</style>