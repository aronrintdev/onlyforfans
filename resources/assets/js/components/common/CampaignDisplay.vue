<template>
  <section v-if="activeCampaign" class="box-campaign-blurb mt-1">
    <h6 v-if="activeCampaign.type==='trial'" class="m-0 text-center">Limited offer - Free trial for {{ activeCampaign.trial_days }} days!</h6>
    <h6 v-if="activeCampaign.type==='discount'" class="m-0 text-center">Limited offer - {{ activeCampaign.discount_percent }}% off for {{ activeCampaign.offer_days }} days!</h6>
    <p class="m-0 text-center"><small class="text-muted">{{ activeCampaign | renderCampaignBlurb }}</small></p>
    <article v-if="activeCampaign.message" class="tag-message d-flex align-items-center">
      <div class="user-avatar">
        <b-img rounded="circle" :src="timeline.avatar.filepath" :title="timeline.name" />
      </div>
      <div class="text-wrap py-2 w-100">
        <p class="mb-0">{{ activeCampaign.message }}</p>
      </div>
    </article>
  </section>
</template>

<script>
/**
 * resources/assets/js/components/common/CampaignDisplay.vue
 */
import Vuex from 'vuex'

export default {
  name: 'CampaignDisplay',

  components: {},

  props: {
    value: { type: Object, default: () => ({}) },
    timeline: { type: Object, default: () => ({}) },
  },

  computed: {},

  data: () => ({
    activeCampaign: null,
  }),

  methods: {
    init() {
      this.activeCampaign = this.value

      if (this.value.id) {
        this.$echo.private(`campaign.${this.value.id}`)
          .listen('Updated', (e) => {
            this.activeCampaign.remaining_count = e.remaining_count
          })
      }
    },
  },

  watch: {},

  mounted() {
    this.init()
  },
}
</script>

<style lang="scss" scoped>
.box-campaign-blurb {

  .tag-message {
    position: relative;
    margin-top: 0.3rem;
    padding: 0.2rem 0.3rem;

    .text-wrap {
      border-radius: 0.5rem;
      background: #f1f1f1;
      margin-left: 5px;
      p { 
        margin-left: 40px;
      }
    }

    .user-avatar {
      position: absolute;
      top: -5px;
      left: 0;
    }

    .user-avatar img {
      object-fit: cover;
      width: 40px;
      height: 40px;
    }
  }
}

</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
