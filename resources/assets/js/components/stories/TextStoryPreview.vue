<template>
  <div class="card card-default">
    <div class="card-header">Preview</div>
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
body main .card {
  height: 70vh;
  max-height: 350px;
}
</style>
