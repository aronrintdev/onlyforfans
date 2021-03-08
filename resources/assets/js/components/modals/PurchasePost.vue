<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <a :href="timelineUrl"><b-img :src="post.user.avatar.filepath" :alt="post.user.name" :title="post.user.name"></b-img></a>
      </section>
      <section class="user-details">
        <div>
          <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ post.user.name }}</a>
          <span v-if="post.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <a :href="timelineUrl" class="tag-username">@{{ post.timeline_slug }}</a>
        </div>
      </section>
    </b-card-header>

    <b-form @submit="purchasePost">

      <b-card-body>

        <b-form-spinbutton
          id="purchase-amount"
          class="w-100 mx-auto tag-purchase_amount"
          v-model="formPayload.amount"
          :formatter-fn="niceCurrency"
          min="5"
          max="100"
          step="5"
          ></b-form-spinbutton>

        <textarea v-model="formPayload.notes" cols="60" rows="5" class="w-100 mt-3" placeholder="Write a message"></textarea>

      </b-card-body>

      <b-card-footer>
        <b-button type="submit" variant="warning" class="w-100">purchase Post</b-button>
      </b-card-footer>

    </b-form>

  </b-card>
</template>

<script>

export default {

  props: {
    session_user: null,
    post: null,
  },

  computed: {
    timelineUrl() {
      return `/timelines/${this.post.slug}`; // DEBUG
      //return `/${this.timeline.slug}`;
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

    niceCurrency(v) {
      //return `$ ${v}`;
      return new Intl.NumberFormat('en-US',
        { style: 'currency', currency: 'USD' }
      ).format(v);
    },

    async purchasePost(e) {
      e.preventDefault();
      const url = `/fanledgers`;
      const response = await axios.post(url, {
        fltype:  'purchase',
        purchaseable_type: 'posts',
        purchaseable_id: this.post.id,
        seller_id: this.post.user_id,
        base_unit_cost_in_cents: this.formPayload.amount * 100,
        notes: this.formPayload.notes || '',
      });

      this.$bvModal.hide('modal-purchase_post');

      this.$root.$bvToast.toast(`Tip sent to ${this.post.timeline_slug}`, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
      });


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
