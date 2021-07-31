<template>
  <div>
    <b-row>
      <aside class="bg-dark col-sm-2">
        <MajorNav />
      </aside>

      <main class="col-sm-10">
        <router-view />
      </main>

    </b-row>
  </div>
</template>

<script>
import Vuex from 'vuex'
import MajorNav from '@views/admin/components/common/MajorNav'

export default {
  name: 'Dashboard',

  components: {},

  props: {},

  computed: {},

  data: () => ({}),

  methods: {},

  watchers: {},

  created() {},

  components: {
    MajorNav,
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
