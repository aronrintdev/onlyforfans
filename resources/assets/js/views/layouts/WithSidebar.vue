<template>
  <div class="container-fluid p-3">
    <section class="d-flex flex-nowrap h-100 w-100">
      <aside class="sidebar">
        <slot name="sidebar"></slot>
      </aside>
      <main class="main flex-fill h-100">
        <slot></slot>
      </main>
    </section>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/layouts/WithSidebar.vue
 */
import Vuex from 'vuex'

export default {
  name: 'WithSidebar',

  components: {},

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.sidebar {
  width: 25rem;
  min-width: 20rem;
}
.main {
  max-height: 100%;
  min-width: 20rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
