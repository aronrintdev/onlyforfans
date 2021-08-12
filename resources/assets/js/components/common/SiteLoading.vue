<template>
  <div class="site-loading d-flex flex-column justify-content-center align-items-center text-brand">
    <Branding type="text" size="3x" class="mb-5" />
    <fa-icon icon="spinner" size="3x" spin />
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/SiteLoading.vue
 */
import Vuex from 'vuex'
import Branding from '@components/common/Branding'

export default {
  name: 'SiteLoading',

  components: {
    Branding,
  },

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.site-loading {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 2000;
  background-color: var(--light);
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
