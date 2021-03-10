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
          :formatter-fn="niceCurrency"
          min="5"
          max="100"
          step="5"
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
    formPayload: {
      amount: 5,
      notes: '',
    },
  }),

  created() {
  },

  methods: {

    async sendTip(e) {
      e.preventDefault();
      const url = `/fanledgers`;
      const response = await axios.post(url, {
        fltype:  'tip',
        purchaseable_type: 'timelines',
        purchaseable_id: this.timeline.id,
        seller_id: this.timeline.user_id,
        base_unit_cost_in_cents: this.formPayload.amount * 100,
        notes: this.formPayload.notes || '',
      });

      this.$bvModal.hide('modal-send_tip');

      this.$root.$bvToast.toast(`Tip sent to ${this.timeline.username}`, {
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
