<template>
  <div class=" w-100 d-flex position-relative">
    <b-btn
      :variant="`outline-${variant}`"
      @click="onSearchClick"
      class="search-button"
      :class="{
        'ml-auto': openDirection === 'left',
        'mr-auto': openDirection === 'right',
        'open': open
      }"
    >
      <fa-icon icon="search" size="lg" />
    </b-btn>
    <CollapseTransition dimension="width">
      <div v-if="open" class="d-flex">
        <b-input ref="input" class="search-input" :class="`border-${variant}`" :value="value" @input="value => $emit('input', value )" />
        <b-btn class="search-close" :variant="`outline-${variant}`" @click="onCloseClick">
          <fa-icon icon="times" size="lg" />
        </b-btn>
      </div>
    </CollapseTransition>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/search/HorizontalOpenInput.vue
 */
import Vuex from 'vuex'
import { CollapseTransition } from "@ivanv/vue-collapse-transition"

export default {
  name: 'HorizontalOpenInput',

  components: {
    CollapseTransition,
  },

  props: {
    variant: { type: String, default: 'primary' },
    /**
     * What direction the search input will open.
     * `left` will have button right aligned and open to the left.
     * `right`
     */
    openDirection: { type: String, default: 'left' },

    value: { type: String, default: ''},
  },

  computed: {},

  data: () => ({
    open: false,
  }),

  methods: {
    onSearchClick() {
      this.open = !this.open
      if (this.open) {
        this.$nextTick(() => {
          this.$refs.input.$el.focus()
        })
      }
    },
    onCloseClick() {
      this.open = false
    },
  },

  watch: {
    open(value) {
      if (value) {
        this.$emit('opening')
      } else {
        this.$emit('input', '')
        this.$emit('closing')
      }
    }
  },

}
</script>

<style lang="scss" scoped>

.search-button {
  &.open {
    border-right: 0;
    border-radius: 50% 0 0 50%;
  }
  transition: border-radius 0.3s ease-in-out;
}
.search-input {
  height: 100%;
  border-left: 0;
  border-right: 0;
  border-radius: 0;
  &:focus {
    box-shadow: 0;
  }
}
.search-close {
  border-left: 0;
  border-radius: 0 50% 50% 0;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
