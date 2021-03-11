<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <a :href="timelineUrl"><b-img :src="timeline.avatar.filepath" :alt="timeline.name" :title="timeline.name"></b-img></a>
      </section>
      <section class="user-details">
        <div>
          <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ timeline.name }}</a>
          <span v-if="timeline.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <a :href="timelineUrl" class="tag-username">@{{ timeline.slug }}</a>
        </div>
      </section>
    </b-card-header>

    <b-form @submit="sendTip">

      <b-card-body>

        <b-form-spinbutton
          id="tip-amount"
          class="w-100 mx-auto tag-tip_amount"
          v-model="formPayload.amount"
          :formatter-fn="$options.filters.niceCurrency"
          min="500"
          max="10000"
          :step="LEDGER_CONFIG.TIP_STEP_DELTA"
          ></b-form-spinbutton>

        <textarea v-model="formPayload.notes" cols="60" rows="5" class="w-100 mt-3" placeholder="Write a message"></textarea>

      </b-card-body>

      <b-card-footer>
        <b-button type="submit" variant="warning" class="w-100">Send Tip</b-button>
      </b-card-footer>

    </b-form>

  </b-card>
</template>

<script>
import { eventBus } from '@/app'
import LEDGER_CONFIG from "@/components/constants"

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    timelineUrl() {
      return `/${this.timeline.slug}`;
    },
  },

  data: () => ({
    LEDGER_CONFIG,
    formPayload: {
      amount: LEDGER_CONFIG.MIN_TIP_IN_CENTS,
      notes: '',
    },
  }),

  created() { },

  methods: {

    async sendTip(e) {
      e.preventDefault();
      const response = await axios.put(route('timelines.tip', this.timeline.id), {
        base_unit_cost_in_cents: this.formPayload.amount,
        notes: this.formPayload.notes || '',
      });
      this.$bvModal.hide('modal-tip');
      this.$root.$bvToast.toast(`Tip sent to ${this.timeline.slug}`, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
      });
      eventBus.$emit('update-timeline', this.timeline.id)
    },

  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}


body .user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

body .user-details .tag-username {
  color: #859AB5;
  text-transform: capitalize;
}
</style>
