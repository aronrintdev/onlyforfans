<template>
  <div>
    <fa-icon :icon="icon" v-bind="$attrs" :class="variantClass" />
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/Branding.vue
 */
import Vuex from 'vuex'
import { allfansCircle } from '@/icons/allfansCircle'
import { allfansType } from '@/icons/allfansType'

export default {
  name: 'Branding',

  components: {},

  props: {
    /** circle | text */
    type: { type: String, default: 'icon' },
    /** Text Variant */
    variant: { type: String, default: 'brand' },
  },

  computed: {
    icon() {
      switch(this.type) {
        default: case 'circle': return allfansCircle
        case 'text': return allfansType
      }
    },
    variantClass() {
      switch (this.variant) {
        case 'gradient': return 'brand-gradient'
        default: return `text-${this.variant}`
      }
    },
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
