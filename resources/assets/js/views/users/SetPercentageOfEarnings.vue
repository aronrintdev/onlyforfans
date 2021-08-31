<template>
  <div class="container-xl" id="view-percentage-of-gross-earnings">
    <div v-if="!isProcessing && !hasError">
      <b-card class="w-50 m-auto">
        <b-card-text>
          <p class="mt-1 mb-3">You have {{ agreed ? 'agreed' : 'disagreed' }} new change for the <em>Percentage of Gross Earnings</em></p>
          <div class="text-right"><router-link :to="{ name: 'index' }">Back To Homepage</router-link></div>
        </b-card-text>
      </b-card>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    agreed: false,
    isProcessing: false,
    hasError: false,
  }),

  created() {
    const { agreed, token } = this.$route.query;
    const { id } = this.$route.params;
    this.agreed = agreed;
    this.isProcessing = true;
    const formData = {
      agreed: agreed == 'true',
      token: token,
    };
    this.axios.patch(this.$apiRoute('staff.changeSettings', id), formData)
      .then(() => {
        this.isProcessing = false;
      })
      .catch(() => {
        this.isProcessing = false;
        this.hasError = true;
        this.$root.$bvToast.toast('Invalid user or token', {
          toaster: 'b-toaster-top-center',
          title: 'Failed!',
          variant: 'danger',
        })
        this.$router.push('/');
      })
  },
}
</script>

<style>
  #view-percentage-of-gross-earnings a {
    font-size: 14px;
    text-decoration: none;
    text-transform: uppercase;
    background: #007bff;
    display: inline-block;
    padding: 8px 24px;
    border: 2px solid transparent;
    border-radius: 40px;
    color: #fff;
    font-weight: 400;
    transition: 0.2s all;
  }
</style>