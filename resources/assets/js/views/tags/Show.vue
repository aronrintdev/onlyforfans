<template>
  <div class="container-xl" id="view-show_tag">
      <b-card>
          <b-card-body>

            <b-card-text>
                <h3>{{ `Tag #` }}</h3>
                
                <div class="mt-5 mb-3">
                    <h5>Posts</h5>
                    <ul>
                        <li>Post 1</li>
                        <li>Post 2</li>
                        <li>Post 3</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h5>Vaultfiles</h5>
                    <ul>
                        <li>File 1</li>
                        <li>File 2</li>
                        <li>File 3</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h5>Mediafiles</h5>
                    <ul>
                        <li>File 1</li>
                        <li>File 2</li>
                        <li>File 3</li>
                    </ul>
                </div>
            </b-card-text>
          </b-card-body>
      </b-card>
  </div>
</template>

<script>

export default {
  props: {
    id: null,
  },

  data: () => ({
    ctag: null
  })
}
</script>

<style scoped>
</style>
