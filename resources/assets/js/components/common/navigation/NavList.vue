<template>
  <b-list-group :flush="mobile">
    <b-list-group-item
      v-for="(item, index) in items"
      :key="item.key"
      :active="isActive(item, index)"
      class="cursor-pointer d-flex align-items-center"
      @click="onClick(item)"
    >
      <fa-icon v-if="item.icon" :icon="item.icon" fixed-width class="mr-2" />
      {{ item.label }}
      <fa-icon v-if="!item.hideCaret" icon="caret-right" fixed-width class="ml-auto" />
    </b-list-group-item>

  </b-list-group>
</template>

<script>
/**
 * resources/assets/js/components/common/navigation/NavList.vue
 */
import Vuex from 'vuex'

export default {
  name: 'NavList',

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
  },

  props: {
    items: { type: Array, default: () => ([]) },
    selected: { type: [ String, Number ], default: '' },
  },

  methods: {
    isActive(item, index) {
      if(item.to && this.$route.name === item.to.name) {
        return true
      }
      return this.selected === item.key || this.selected === index
    },
    onClick(item) {
      if(item.to) {
        this.$router.push(item.to)
      }
      this.$emit('select', item)
    },
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
