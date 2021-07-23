<template>
  <div
    class="w-100 d-flex position-relative search-component"
    :class="[
      {
        'no-borders': !borders,
        open
      },
      `border-${variant}`
    ]"
  >
    <b-btn
      :variant="borders ? `outline-${variant}` : 'link'"
      @click="onSearchClick"
      class="search-button"
      :class="[{
        'ml-auto': openLeft,
        'open': open
      }, `border-${variant}`]"
    >
      <fa-icon v-if="size" icon="search" :size="size" />
      <fa-icon v-else icon="search" />
    </b-btn>
    <div class="w-100">
     <CollapseTransition dimension="width">
        <div v-if="open" class="d-flex">
          <b-input
            ref="input"
            class="search-input"
            :class="[{ 'border-bottom': !borders && open }, `border-${variant}`]"
            :value="value" @input="value => $emit('input', value )"
          />
          <b-btn
            class="search-close"
            :class="[{ 'border-bottom': !borders && open }, `border-${variant}`]"
            :variant="borders ? `outline-${variant}` : 'link'"
            @click="onCloseClick"
          >
            <fa-icon v-if="size" icon="times" :size="size" />
            <fa-icon v-else icon="times" />
          </b-btn>
        </div>
      </CollapseTransition>
    </div>
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
    borders: { type: Boolean, default: false },

    variant: { type: String, default: 'primary' },
    /**
     * What direction the search input will open.
     * `left` will have button right aligned and open to the left.
     * `right`
     */
    openLeft: { type: Boolean, default: false },
    size: { type: String, default: null },

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
.no-borders {
  .search-button {
    border: 0;
    &.open {
      border-radius: 50% 0 0 0;
    }
  }
  .search-input {
    border: 0;
  }
  .search-close {
    border: 0;
    border-radius: 0 50% 0 0;
  }
  transition: border-bottom 0.3s ease-in-out;
}

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
