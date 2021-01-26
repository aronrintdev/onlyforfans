<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <a :href="timelineUrl"><b-img :src="timeline.user.avatar.filepath" :alt="timeline.user.name" :title="timeline.user.name"></b-img></a>
      </section>
      <section class="user-details">
        <div>
          <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ timeline.user.name }}</a>
          <span v-if="timeline.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <a :href="timelineUrl" class="tag-username">@{{ timeline.username }}</a>
        </div>
      </section>
    </b-card-header>

    <b-form @submit="sendTip">

      <b-card-body>

        <b-form-spinbutton
          id="tip-amount"
          v-model="formPayload.amount"
          min="3"
          max="100"
          step="3"
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

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    timelineUrl(username) {
      return `/timelines/${this.timeline.username}`; // DEBUG
      //return `/${this.timeline.username}`;
    },
  },

  data: () => ({
    formPayload: {
      amount: 3,
      notes: '',
    },
  }),

  created() {
  },

  methods: {

    async sendTip(e) {
      e.preventDefault();

      console.log('toast');
      $bvToast.show('example-toast');
      return false;

      const url = `/fanledgers`;
      const response = await axios.post(url, {
        fltype:  'tip',
        purchaseable_type: 'timelines',
        purchaseable_id: this.timeline.id,
        seller_id: this.timeline.user.id,
        base_unit_cost_in_cents: this.formPayload.amount * 100,
        notes: this.formPayload.notes || '',
      });

      // close modal, confirm message

    },

    async submitComment(e) {
      e.preventDefault();
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
