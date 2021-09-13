<template>
  <b-modal
    :visible="visible"
    hide-footer
    :title="$t('title')"
    @hide="onHide"
  >
    <div class="d-flex flex-column" @keypress.enter="addTip">
      <!-- Avatar -->
      <div class="mb-3">
        <AvatarWithStatus :user="receiver" size="md" />
      </div>

      <!-- Tip Amount -->
      <div class="mb-3">
        <TipInput ref="input" v-model="tip" @isValid="onIsValid" />
      </div>
      <!-- Add Button -->
      <div>
        <b-btn variant="primary" block @click="addTip">
          {{ $t('submitButton') }}
        </b-btn>
      </div>
    </div>
  </b-modal>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/AddTip.vue
 * Form for adding a tip to a message
 */
import AvatarWithStatus from '@components/user/AvatarWithStatus'
import TipInput from '@components/forms/elements/TipInput'

export default {
  name: 'AddTip',

  components: {
    AvatarWithStatus,
    TipInput,
  },

  model: {
    prop: 'visible',
  },

  props: {
    /** User receiving the tip */
    receiver: { type: Object, default: () => ({}) },
    visible: { type: Boolean, default: false },
  },

  computed: {},

  data: () => ({
    tip: { amount: 0, currency: 'USD' },
    isValid: true,
  }),

  methods: {
    addTip() {
      console.log(this.$refs['input'].$refs['input'].validate())
      if (this.$refs['input'].$refs['input'].validate() === true) {
        // false === 'invalid' null === 'valid'
        if (this.isValid !== false) {
          if (this.tip.amount === 0) {
            this.tip.amount = 500
          }
          this.$emit('submit', this.tip)
          this.onHide()
        }
      }
    },

    onIsValid(value) {
      this.isValid = value
    },

    onHide() {
      // Hides modal
      this.$emit('input', false)
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "submitButton": "Add Tip",
    "title": "Add Tip"
  }
}
</i18n>
