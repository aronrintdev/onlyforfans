<template>
  <b-list-group :flush="mobile">
    <b-list-group-item
      v-for="(item, index) in items"
      :key="item.key"
      :selected="selected === item.key || selected === index"
      class="cursor-pointer d-flex align-items-center"
      @click="$emit('select', item)"
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
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
