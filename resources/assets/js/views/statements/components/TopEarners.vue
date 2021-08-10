<template>
  <div>
    <b-alert variant="success" :show="show" class="py-4" >
      <b-media>
        <template #aside>
          <fa-icon icon="star" size="2x" fixed-width />
        </template>
        <span class="text-uppercase font-weight-bold" v-text="$t('message', { percent })" />
      </b-media>
    </b-alert>

  </div>
</template>

<script>
/**
 * resources/assets/js/views/statements/components/TopEarners.vue
 */
import Vuex from 'vuex'

export default {
  name: 'TopEarners',

  components: {},

  props: {
    show: { type: Boolean, default: true },
  },

  computed: {},

  data: () => ({
    percent: 100,
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "message": "You are in the top {percent}% of all creators!"
  }
}
</i18n>
