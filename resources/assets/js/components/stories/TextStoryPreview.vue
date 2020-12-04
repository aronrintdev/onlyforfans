<template>
  <div class="card card-default">
    <div class="card-header">Preview ({{ username }})</div>
    <div v-bind:style="{ backgroundColor: attrs.color }" class="card-body">
      {{ attrs.contents }}
    </div>
  </div>
</template>

<script>
export default {
  mounted() {

  },

  props: [
    'attrs',
    'username',
  ],

  data: () => ({
  }),

  methods: {
  },

  components: {
  },
}
</script>

<style scoped>
</style>
