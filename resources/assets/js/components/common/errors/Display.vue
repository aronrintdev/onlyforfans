<template>
  <div class="d-flex w-100">
    <b-card class="error-card mx-auto align-self-center">
      <template #header>
        <div class="h3 text-center">{{ title }}</div>
      </template>
      <div class="text-center mt-3 mb-5">
        <b-alert variant="danger" show>
          {{ message }}
        </b-alert>
      </div>
      <div class="d-flex justify-content-center my-1">
        <b-btn v-if="canGoBack()" variant="primary" class="mx-3" @click="goBack">Back</b-btn>
        <b-btn variant="primary" href="/">Return to Home</b-btn>
      </div>
    </b-card>
  </div>
</template>

<script>
/**
 * Display card for an error
 */
export default {
  props: {
    title: { type: String, },
    message: { type: String },
  },
  methods: {
    canGoBack() {
      return window.history.length > 1
    },
    goBack() {
      this.$router.go(-1)
    }
  }
}
</script>

<style lang="scss" scoped>
.error-card {
  width: 750px;
}
</style>
