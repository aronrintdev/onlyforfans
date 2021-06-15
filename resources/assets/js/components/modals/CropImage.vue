<template>
  <b-card no-body>
    <transition name="quick-fade" mode="out-in">
      <b-card-body>
        <vue-croppie
          ref="croppieRef"
          :enableResize="false"
          :boundary="{ width: 300, height: 300 }"
          :viewport="{ width: 240, height: 240, type: 'circle' }"
        >
        </vue-croppie>
      </b-card-body>
    </transition>
    <transition name="quick-fade" mode="out-in">
      <b-card-footer>
        <b-button @click="crop" :disabled="isProcessing" variant="primary" class="w-100">
          <b-spinner v-if="isProcessing" small></b-spinner>&nbsp;
          Done
        </b-button>
      </b-card-footer>
    </transition>
  </b-card>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import base64ToBlob from '@helpers/base64ToBlob'

export default {
  components: {},

  props: {
    url: null,
    timelineId: null,
  },

  computed: {},

  mounted() {
    this.$refs.croppieRef.bind({ url: this.url })
  },

  data: () => ({
    isProcessing: false,
  }),

  methods: {
    ...Vuex.mapActions(['getMe']),

    crop() {
      // Options can be updated.
      // Current option will return a base64 version of the uploaded image with a size of 240px X 240px.
      let options = {
        circle: false,
        type: 'base64',
        size: { width: 240, height: 240 },
        format: 'jpeg',
      }
      this.$refs.croppieRef.result(options, (output) => {
        const blob = base64ToBlob(output.substr(23), 'image/jpeg')
        const formData = new FormData()
        formData.append('avatar', blob)

        this.isProcessing = true
        this.axios
          .post('/users/avatar', formData)
          .then((response) => {
            this.isProcessing = false

            // update the avatar in the current timeline
            eventBus.$emit('update-timelines', this.timelineId)

            // update the avatar on the profile menu
            this.getMe()

            // hide modal
            this.$bvModal.hide('modal-crop')
          })
          .catch((error) => {
            this.$log.error(error)
          })
      })
    },
  },
}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}
</style>
